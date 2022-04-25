<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\BaseController;
use app\library\RedisHash;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use think\db\exception\PDOException;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Env;
use think\facade\Filesystem;
use WebPConvert\WebPConvert;

/**
 * 后台接口基类
 */
class Base extends BaseController
{
    /**
     * 管理员信息
     * @var array
     */
    protected $adminInfo = [];

    /**
     * 需要额外加入的请求数据，
     * @var array
     */
    protected $params = [];

    /**
     * 查询过滤字段，需要的字段请写入
     * @var array
     */
    protected $field = [];

    /**
     * 定义精准搜索条件
     * @var array
     */
    protected $focus = [];

    /**
     * 定义模糊搜索条件
     * @var array
     */
    protected $vague = [];

    /**
     * 定义排序字段
     * @var array
     */
    protected $order = [];

    /**
     * 分页页码
     * @var integer
     */
    protected $page = 0;

    /**
     * 分页数量
     * @var integer
     */
    protected $size = 0;

    /**
     * 导入文件首行类型
     * 支持comment/name
     * 表示注释或字段名默认为字段注释
     */
    protected $importHeadType = 'comment';

    /**
     * 模型单例
     * @var null
     */
    protected $model = null;

    /**
     * 验证器单例
     * @var null
     */
    protected $validate = null;

    /**
     * 返回给客户端请求的数据
     * @var array
     */
    protected $returnData = [];

    /**
     * http返回状态码，200表示请求成功，504表示 请求失败
     * @var array
     */
    protected $status = 504;

    /**
     * 提示信息
     * @var string
     */
    protected $msg = '';

    /**
     * 返回给前端的状态码。0表示请求数据失败，1表示请求数据成功
     * @var int
     */
    protected $code = 1;

    /**
     * 反馈开发提示信息
     * @var string
     */
    protected $sysMsg = [
        'ERROR','SUCCESS'
    ];

    /**
     * 定义JWT
     * @var null
     */
    protected $jwt = null;

    /**
     * 接收请求的数据
     * @var array
     */
    protected $inputData = [];

    /**
     * 请求的字段名
     * @var array
     */
    protected $inputField = [];

    /**
     * 初始化方法
     */
    public function initialize()
    {
        /**
         * 全局接收请求的参数
         */
        if (!empty($this->inputField)) {
            $tmpField = $this->inputField;
        } else {
            $tmpField = '';
        }
        $this->inputData = $this->request->param($tmpField);
        /**
         * 回写管理员信息
         */
        $this->adminInfo = [
            'admin_id' => $this->request->uid,
            'role_key' => $this->request->role_key,
            'user_info' => $this->request->user_info
        ];
    }

    /**
     * 处理数据结构
     * @param array $data   请求接收到的数据
     * @param array $name   数据库字段，需要查询的字段名称
     * @param string $type  构建查询数据类型，只有3种。vague=模糊，focus=准确，order=排序
     * @param array $condition  条件，比如此字段值不能为空，或者不能等于0之类的。['',0],主要是前端请求提交过来的值，当这个条件成立的时候，相应搜索条件不成立
     * @return bool
     */
    public function doDataStructure(array $data,array $name,string $type = 'vague',array $condition = ['','0']):bool
    {
        if (empty($data) || empty($condition) || empty($type) || empty($name)) {
            return false;
        }
        //定义type的类型，只有3种。模糊，准确，排序
        if (in_array($type,['vague','focus','order'])) {
            foreach ($name as $value) {
                if (isset($data[$value]) && !in_array($data[$value],$condition,true)) {
                    $this->$type[$value] = $data[$value];
                }
            }
            return true;
        }
        return false;
    }

    /**
     * 公共方法验证器
     * @param string $sceneName 对应场景
     * @param array $data   需要验证的数据，数组结构
     */
    public function commonValidate(string $sceneName,array $data) :bool
    {
        if(!$this->validate->scene($sceneName)->check($data)) {
            if (false === strrpos($this->validate->getError(),'|')) {
                $this->code = -1;
                $this->msg  = $this->validate->getError();
            } else {
                $err = explode('|',$this->validate->getError());
                $this->code = $err[1];
                $this->msg  = $err[0];
            }
            return true;
        }
        return false;
    }

    /**
     * 公共方法返回数据结构
     * @param bool $validate    表示是否是验证器异常信息
     */
    public function message(bool $validate = false): \think\Response\Json
    {
        $this->sysMsg[1] = $this->sysMsg[1]  ?? 'invalid';
        $this->sysMsg[$this->code] = $this->sysMsg[$this->code] ?? 'validate invalid';

        $data = [
            'status'        => $this->status,
            'code'          => $this->code,
            'result'        => $this->returnData,
            'message'       => $this->msg,
            'type'          => $validate ? $this->sysMsg[0] : $this->sysMsg[$this->code]
        ];
        return json($data);
    }

    /**
     * 公共的返回数据接口
     * @param array|string $msg     返回的消息
     * @param array|bool $result    返回的结果
     * @param bool $validate        是否是验证器
     */
    public function jr($msg,$result = false,bool $validate = false): \think\Response\Json
    {
        if (is_array($msg)) {
            if (count($msg) === 2) {
                $this->msg  = $result ? $msg[1] : $msg[0];
            } else {
                //如果只传一个值。
                $this->msg = $msg[0];
            }
        } elseif (is_string($msg)) {
            $this->msg = $msg;
        } else {
            $this->msg = 'error invalid';
        }
        $this->code     = $result ? 0 : 1;
        $this->status   = $result ? 200 : 504;
        $this->returnData = !is_array($result) ? [] : $result;
        return $this->message($validate);
    }

    /**
     * 显示资源列表
     */
    public function index() :\think\Response
    {
        if (!empty($this->params)) {
            $this->inputData = array_merge($this->inputData,$this->params);
        }
        //判断是否需要分页
        if (isset($this->inputData['page']) && $this->inputData['page'] != 0) {
            $this->page = (int)$this->inputData['page'];
        }
        if (isset($this->inputData['size']) && $this->inputData['size'] != 0) {
            $this->size = (int)$this->inputData['size'];
        }

        $result = $this->model->getIndexList($this->page,$this->size,$this->field,$this->vague,$this->focus,$this->order);
        //构建返回数据结构
        return $this->jr('获取成功',!empty($result) ? $result : []);
    }

    /**
     * 显示指定的资源
     */
    public function read():\think\Response\Json
    {
        //前置拦截
        if (!isset($this->inputData['id']) || (int)$this->inputData['id'] <= 0) {
            return $this->jr('请输入需要获取的id值');
        }
        //额外增加请求参数
        if (!empty($this->params)) {
            $this->inputData = array_merge($this->inputData,$this->params);
        }
        if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
            return $this->message(true);
        }
        $result = $this->model->getInfo((int)$this->inputData['id']);
        return $this->jr(['获取失败','获取成功'],$result);
    }

    /**
     * 保存新建的资源
     */
    public function save():\think\Response
    {
        //前置拦截
        if (empty($this->inputData)) {
            return $this->jr('请检查提交过来的数据');
        }
        //额外增加请求参数
        if (!empty($this->params)) {
            $this->inputData = array_merge($this->inputData,$this->params);
        }
        if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
            return $this->message(true);
        }
        $result = $this->model->addData($this->inputData);
        return $this->jr(['新增失败','新增成功'],$result);
    }

    /**
     * 保存更新的资源
     */
    public function update():\think\Response\Json
    {
        //前置拦截
        if (!isset($this->inputData['id']) || (int)$this->inputData['id'] <= 0) {
            return $this->jr('请输入正确的需要修改的ID值');
        }
        //额外增加请求参数
        if (!empty($this->params)) {
            $this->inputData = array_merge($this->inputData,$this->params);
        }
        if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
            return $this->message(true);
        }
        $result = $this->model->editData($this->inputData);
        return $this->jr(['修改失败','修改成功'],$result);
    }

    /**
     * 删除指定资源
     */
    public function delete():\think\Response\Json
    {
        //前置拦截
        if (!isset($this->inputData['id']) || (int)$this->inputData['id'] <= 0) {
            return $this->jr('请输入需要删除的ID值');
        }
        //额外增加请求参数
        if (!empty($this->params)) {
            $this->inputData = array_merge($this->inputData,$this->params);
        }
        if ($this->commonValidate(__FUNCTION__,$this->inputData)) {
            return $this->message(true);
        }
        $result = $this->model->delData($this->inputData);
        return $this->jr(['删除失败','删除成功'],$result);
    }

    /**
     * 图片，ICON上传
     */
    public function upload():\think\Response\Json
    {
        $files = $this->request->file();
        if (!$files) {
            return $this->jr("请选择上传的文件");
        }
        $field = array_keys($files)[0];
        $data = [];
        //多上传名称，单图上传  //不同字段文件名上传 image img icon
        if (is_array($files) && count($files) > 1 ) {
            foreach ($files as $key => $fileSimple) {
                if ($this->commonValidate(__FUNCTION__, [$key => $fileSimple])) {
                    return $this->jr($this->validate->getError());
                }
                //上传本地
                $filename = Filesystem::putFile(public_path().'uploads',$fileSimple);
                $source = app()->getRootPath() . 'public/storage/'.$filename;
                $destination = $source . '.webp';
                $options = [];
                WebPConvert::convert($source, $destination, $options);
                $data[] = [
                    'cdn'           => Env::get('QINIU.cdn'),
                    'origin_name'   => $fileSimple->getOriginalName(),
                    'filename'       => $filename,
                    'md5'           => md5_file($files[$field]->getPathname()),
                    'url'           => $this->request->domain(true).'/storage/'  . $filename,
                    'relative_path' => 'storage/'  . $filename,
                    'webp_path'     => 'storage/'  . $destination
                ];
            }
        // 单上传名称，多图上传 同一名称的数组文件上传.image[0] image[1]
        } elseif (is_array($files[$field]) && count($files[$field]) > 1) {

            foreach ($files as $key => $fileSimple) {
                if ($this->commonValidate(__FUNCTION__, [$key => $fileSimple])) {
                    return $this->jr($this->validate->getError());
                }
                //上传本地
                $filename = Filesystem::disk('public')->putFile('', $fileSimple, 'unique_id');
                $source = app()->getRootPath() . 'public/storage/'.$filename;
                $destination = $source . '.webp';
                $options = [];
                WebPConvert::convert($source, $destination, $options);
                $data[] = [
                    'cdn'           => Env::get('QINIU.cdn'),
                    'origin_name'   => $fileSimple->getOriginalName(),
                    'filename'       => $filename,
                    'md5'           => md5_file($files[$field]->getPathname()),
                    'url'           => $this->request->domain(true).'/storage/'  . $filename,
                    'relative_path' => 'storage/'  . $filename,
                    'webp_path'     => 'storage/'  . $destination
                ];
            }
        // 单名称，单图上传
        } else {
            if ($this->commonValidate(__FUNCTION__,[$field => $files])) {
                return $this->jr($this->validate->getError());
            }
            //上传本地
            $filename = Filesystem::disk('public')->putFile('', $files[$field], 'unique_id');
            $source = app()->getRootPath() . 'public/storage/'.$filename;
            $destination = $source . '.webp';
            $options = [];
            WebPConvert::convert($source, $destination, $options);
            //上传到七牛
            $data = [
                'cdn'           => Env::get('QINIU.cdn'),
                'origin_name'   => $files[$field]->getOriginalName(),
                'filename'       => $filename,
                'md5'           => md5_file($files[$field]->getPathname()),
                'url'           => $this->request->domain(true).'/storage/'  . $filename,
                'relative_path' => 'storage/'  . $filename,
                'webp_path'     => 'storage/'  . $destination
            ];
        }

        return $this->jr('上传成功',$data);
    }

    /**
     * Excel导入
     */
    public function import():\think\Response\Json
    {
        $file = $this->request->file('file');
        if (!$file) {
            return $this->jr('没有上传文件');
        }
//        if ($this->commonValidate(__FUNCTION__,['file'=>$file])) {
//            return $this->message(true);
//        }
        $filename = Filesystem::disk('public')->putFile('', $file, 'unique_id');
        $filePath = public_path().'storage/'  . $filename;
        if (!is_file($filePath)) {
            return $this->jr('没找到数据');
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            return $this->jr('文件格式不对');
        }
        if ($ext === 'csv') {
            $file = fopen($filePath, 'r');
            $filePath = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp = fopen($filePath, "w");
            $n = 0;
            while ($line = fgets($file)) {
                $line = rtrim($line, "\n\r\0");
                $encoding = mb_detect_encoding($line, ['utf-8', 'gbk', 'latin1', 'big5']);
                if ($encoding != 'utf-8') {
                    $line = mb_convert_encoding($line, 'utf-8', $encoding);
                }
                if ($n == 0 || preg_match('/^".*"$/', $line)) {
                    fwrite($fp, $line . "\n");
                } else {
                    fwrite($fp, '"' . str_replace(['"', ','], ['""', '","'], $line) . "\"\n");
                }
                $n++;
            }
            fclose($file) || fclose($fp);

            $reader = new Csv();
        } elseif ($ext === 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }
        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $table = $this->model->getTable();
        $database = Env::get('database.database');
        $fieldArr = [];
        $list = Db::query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        foreach ($list as $v) {
            if ($this->importHeadType == 'comment') {
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }
        //加载文件
        $insert = [];
        try {
            if (!$PHPExcel = $reader->load($filePath)) {
                return $this->jr('没找到数据');
            }
            $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
            $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
            $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
            $maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
            $fields = [];
            for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $fields[] = $val;
                }
            }
            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $values = [];
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $values[] = is_null($val) ? '' : $val;
                }
                $row = [];
                $temp = array_combine($fields, $values);
                foreach ($temp as $k => $v) {
                    if (isset($fieldArr[$k]) && $k !== '') {
                        $row[$fieldArr[$k]] = $v;
                    }
                }
                if ($row) {
                    $insert[] = $row;
                }
            }
            //需要关联查询的字段，进行关联相询翻译
        } catch (\Exception $exception) {
            $this->msg = $exception->getMessage();
            return json($this->message());
        }

        //批量新增
        try {
            $count = 0;
            $failCount = 0;
//            foreach ($insert as $item) {
//                if ($this->commonValidate(__FUNCTION__,$item)) {
//                    $failCount++;
//                    continue;
//                }
            $res = $this->model->save($insert[0]);
//                $count++;
//            }

            if (count($insert) > $count) {
                $this->code = 0;
                $this->status = 504;
                $this->msg = '总共【'.count($insert).'】，成功导入【'.$count.'】条记录,还有【'.(count($insert) - $count).'】条记录未导入成功';
            } else {
                $this->code = 1;
                $this->status = 200;
                $this->msg = '总共【'.count($insert).'】，成功导入【'.$count.'】条记录,有【'.(count($insert) - $count).'】条记录未导入成功';
            }
            return json($this->message());

        } catch (PDOException $exception) {
            $this->msg = $exception->getMessage();
            if (preg_match("/.+Integrity constraint violation: 1062 Duplicate entry '(.+)' for key '(.+)'/is", $this->msg, $matches)) {
                $this->msg = "导入失败，包含【{$matches[1]}】的记录已存在";
            }
            $this->code = 0;
            $this->status = 504;
            return json($this->message());
        } catch (\Exception $e) {
            $this->code = 0;
            $this->status = 504;
            $this->msg = $e->getMessage();
            return json($this->message());
        }
    }

    /**
     * Excel导出，
     * @param array $data
     * @param int $count
     * @param string $fileName
     * @param array $options
     * @return \think\Response\Json
     */
    protected function excelExport(array $data = [], int $count = 10 ,string $fileName = '', array $options = []):\think\Response\Json
    {
        try {
            if (empty($data)) {
                $this->msg = '没有选择需要导出的数据！';
                return json($this->message());
            }
            set_time_limit(0);
            $objSpreadsheet = new Spreadsheet();
            //设置全局字体，大小
            $styleArray = [
                'font' => [
                    'bold' => false,
                    'color' => ['rgb'=>'000000'],
                    'size' => 14,
                    'name' => 'Verdana'
                ]
            ];
            $objSpreadsheet->getDefaultStyle()->applyFromArray($styleArray);
            /* 设置默认文字居左，上下居中 */
            $styleArray = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ];
            $objSpreadsheet->getDefaultStyle()->applyFromArray($styleArray);
            /* 设置Excel Sheet */
            $activeSheet = $objSpreadsheet->setActiveSheetIndex(0);

            /* 打印设置 */
            if (isset($options['print']) && $options['print']) {
                /* 设置打印为A4效果 */
                $activeSheet->getPageSetup()->setPaperSize(PageSetup:: PAPERSIZE_A4);

                /* 设置打印时边距 */
                $pValue = 1 / 2.54;
                $activeSheet->getPageMargins()->setTop($pValue / 2);
                $activeSheet->getPageMargins()->setBottom($pValue * 2);
                $activeSheet->getPageMargins()->setLeft($pValue / 2);
                $activeSheet->getPageMargins()->setRight($pValue / 2);
            }

            $row = 2;
            $col = 0;
            /* 行数据处理 */
            foreach ($data as $sKey => $sItem) {
                /* 默认文本格式 */
                $pDataType = DataType::TYPE_STRING;
                /* 设置单元格格式 */
                if (isset($options['format']) && !empty($options['format'])) {
                    $colRow = Coordinate::coordinateFromString($sKey);

                    /* 存在该列格式并且有特殊格式 */
                    if (isset($options['format'][$colRow[0]]) &&
                        NumberFormat::FORMAT_GENERAL != $options['format'][$colRow[0]]) {
                        $activeSheet->getStyle($sKey)->getNumberFormat()
                            ->setFormatCode($options['format'][$colRow[0]]);

                        if (false !== strpos($options['format'][$colRow[0]], '0.00') &&
                            is_numeric(str_replace(['￥', ','], '', $sItem))) {
                            /* 数字格式转换为数字单元格 */
                            $pDataType = DataType::TYPE_NUMERIC;
                            $sItem     = str_replace(['￥', ','], '', $sItem);
                        }
                    } elseif (is_int($sItem)) {
                        $pDataType = DataType::TYPE_NUMERIC;
                    }
                }

                if ($col < count($options['alignCenter'])) {
                    if (strlen($sItem) <= 255) {
                        $activeSheet->getColumnDimension($options['alignCenter'][$col])->setWidth(100);
                    } else {
                        $activeSheet->getColumnDimension($options['alignCenter'][$col])->setAutoSize(true);
                    }
                }
                $activeSheet->getRowDimension($row)->setRowHeight(30);
                $activeSheet->setCellValueExplicit($sKey, $sItem, $pDataType);
                $row++;
                $col++;
                /* 存在:形式的合并行列，列入A1:B2，则对应合并 */
                if (false !== strstr($sKey, ":")) {
                    $options['mergeCells'][$sKey] = $sKey;
                }
                if (isImage(public_path().$sItem) && file_exists(public_path().$sItem)) {
                    $activeSheet->setCellValueExplicit($sKey, '', $pDataType);
                    $drawing = new Drawing();
                    $drawing->setName('Logo');
                    $drawing->setDescription('Logo');
                    $drawing->setPath(Env::get('root_path').'public'.$sItem);
                    $drawing->setResizeProportional(false);
                    $drawing->setHeight(60);
                    $drawing->setCoordinates($sKey);
                    $drawing->setOffsetX(12);
                    $drawing->setOffsetY(12);
                    $drawing->getShadow()->setVisible(true);
//                    $drawing->getShadow()->setDirection(45);
                    $drawing->setWorksheet($objSpreadsheet->getActiveSheet());
                }
            }
            unset($data);
            /* 设置锁定行 */
            if (isset($options['freezePane']) && !empty($options['freezePane'])) {
                $activeSheet->freezePane($options['freezePane']);
                unset($options['freezePane']);
            }
            /* 设置宽度 */
            if (isset($options['setWidth']) && !empty($options['setWidth'])) {
                foreach ($options['setWidth'] as $swKey => $swItem) {
                    $activeSheet->getColumnDimension($swKey)->setWidth($swItem);
                }
                unset($options['setWidth']);
            } else {
                $end = $count + 64 > 80 ? 80 : $count + 64;
                foreach(range(chr(65),chr($end)) as $columnID) {
                    $activeSheet->getColumnDimension($columnID)->setAutoSize(true);
                }
            }
            /* 设置背景色 */
            if (isset($options['setARGB']) && !empty($options['setARGB'])) {
                foreach ($options['setARGB'] as $sItem) {
                    $activeSheet->getStyle($sItem)
                        ->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB(Color::COLOR_YELLOW);
                }

                unset($options['setARGB']);
            }
            /* 设置公式 */
            if (isset($options['formula']) && !empty($options['formula'])) {
                foreach ($options['formula'] as $fKey => $fItem) {
                    $activeSheet->setCellValue($fKey, $fItem);
                }

                unset($options['formula']);
            }
            /* 合并行列处理 */
            if (isset($options['mergeCells']) && !empty($options['mergeCells'])) {
                $activeSheet->setMergeCells($options['mergeCells']);
                unset($options['mergeCells']);
            }
            /* 设置居中 */
            if (isset($options['alignCenter']) && !empty($options['alignCenter'])) {
                $styleArray = [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ];

                foreach ($options['alignCenter'] as $acItem) {
                    $activeSheet->getStyle($acItem)->applyFromArray($styleArray);
                }

                unset($options['alignCenter']);
            }
            /* 设置加粗 */
            if (isset($options['bold']) && !empty($options['bold'])) {
                foreach ($options['bold'] as $bItem) {
                    $activeSheet->getStyle($bItem)->getFont()->setBold(true);
                }

                unset($options['bold']);
            }
            /* 设置单元格边框，整个表格设置即可，必须在数据填充后才可以获取到最大行列 */
            if (isset($options['setBorder']) && $options['setBorder']) {
                $border    = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN, // 设置border样式
                            'color'       => ['argb' => 'FF000000'], // 设置border颜色
                        ],
                    ],
                ];
                $setBorder = 'A1:' . $activeSheet->getHighestColumn() . $activeSheet->getHighestRow();
                $activeSheet->getStyle($setBorder)->applyFromArray($border);
                unset($options['setBorder']);
            }

            $fileName = !empty($fileName) ? $fileName : (date('YmdHis') . '.xlsx');

            if (!isset($options['savePath'])) {
                /* 直接导出Excel，无需保存到本地，输出07Excel文件 */
                header('Content-Type: application/vnd.ms-excel,application/x-rar-compressed,application/vnd.openxmlformats-officedocument.wordprocessingml.document; Charset=UTF-8');
                header('Access-Control-Expose-Headers: Content-Disposition');
                header(
                    "Content-Disposition:attachment;filename=" . iconv(
                        "utf-8", "GB2312//TRANSLIT", $fileName
                    )
                );
                header('Cache-Control: max-age=0');//禁止缓存
                header("Content-Transfer-Encoding:binary");
                $savePath = 'php://output';
            } else {
                $savePath = $options['savePath'];
            }
            ob_clean();
            ob_start();
            $objWriter = IOFactory::createWriter($objSpreadsheet, 'Xlsx');
            $objWriter->save($savePath);
            /* 释放内存 */
            $objSpreadsheet->disconnectWorksheets();
            unset($objSpreadsheet);
            ob_end_flush();
            exit;
        } catch (\Exception $e) {
            $this->msg = $e->getMessage();
            return json($this->message());
        }
    }

    /**
     * 表格导出数据前置方法
     */
    public function export():\think\Response\Json
    {
        $ids = $this->request->param();
        if ($this->commonValidate(__FUNCTION__,$ids)) {
            return json($this->message(true));
        }
        $data = $this->model->getExportData($ids);
        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $this->importHeadType = 'name';
        $table = $this->model->getTable();
        $database = Env::get('database.database');
        //字段名与注释的数组，
        $fieldArr = [];
        $list = Db::query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        foreach ($list as $v) {
            if ($this->importHeadType == 'comment') {
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_COMMENT'];
            }
        }

        $newData = [];/*表格数据*/
        $newHeader = [];/*表格表头*/
        $cols = []; /*分别占用表格哪几列*/
        if (array_key_exists(0,$data)) {
            $header = array_keys($data[0]);
            foreach ($header as $k => $v) {
                if ($k <= 25) {
                    $newHeader[chr($k+65).'1'] = $fieldArr[$v]?:$v;
                    $cols[$k] = chr($k+65);
                } else {
                    $newHeader[chr(65).chr($k-26+65).'1'] = $fieldArr[$v]?:$v;
                    $cols[$k] = chr(65).chr($k-26+65);
                }

            }
        }

        foreach ($data as $k => $v) {
            $index = 0;
            foreach ($v as $v1) {
                if ($index <= 25) {
                    $header = chr($index+65);
                    $header .= $k+2;
                    $newData[$header] = $v1;/*获取表头*/
                } else {
                    $header = chr(65).chr($index-26+65);
                    $header .= $k+2;
                    $newData[$header] = $v1;/*获取表头*/
                }
                $index++;
            }
        }
        $newData = array_merge($newHeader,$newData);
        $options = [
            'print' =>false,
            'freezePane'=>'A2',
//            'setWidth'=>['A'=>40,'B'=>30,'C'=>20,'D'=>25,'E'=>20,'F'=>15,'G'=>10,'H'=>10],
            'setBorder'=>true,
            'alignCenter'=>$cols,
            'bold'=>array_keys($newHeader),
        ];
        return $this->excelExport($newData,count($newHeader),'export-excel-'.time().'.xlsx',$options);
    }

    /**
     * curl请求
     * @param $url  string 请求的url链接
     * @param $data string|array|mixed 请求的数据
     * @param bool $is_post 是否是post请求，默认false
     * @param array $options 是否附带请求头
     * @return array|mixed
     */
    public function http(string $url, array $data, bool $is_post=false, array $options=[]):array
    {
        $data  = json_encode($data);
        $headerArray = [
            'Content-type: application/json;charset=utf-8',
            'Accept: application/json'
        ];
        $curl = curl_init();
        $arr = [];
        array_push($arr,$url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,false);
        if ($is_post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($options['cookie'])) {
            curl_setopt($curl, CURLOPT_COOKIE, $options['cookie']);
        } else {
            $headerArray = array_merge($headerArray,$options);
        }
        curl_setopt($curl,CURLOPT_HTTPHEADER,$headerArray);
        $output = curl_exec($curl);
        $http_status = curl_errno($curl);
        $http_msg = curl_error($curl);
        curl_close($curl);
        if ($http_status == 0) {
            return json_decode($output, true);
        } else {
            return ['status' => $http_status, 'message' => $http_msg, 'data' => []];
        }
    }

    /**
     * 打印调试信息到日志
     * @param $data
     * @param string $string
     */
    public function dLog($data, string $string = 'debug'):void
    {
        $newData = [];
        if (is_array($data)) {
            $newData = json_encode($data);
        } else {
            $newData = $data;
        }
        \think\facade\Log::record($string. '==' . $newData);
    }
}
