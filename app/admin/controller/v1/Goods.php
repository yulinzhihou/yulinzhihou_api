<?php
declare (strict_types = 1);

namespace app\admin\controller\v1;

use app\admin\Controller\Base;
use app\admin\model\Goods as GoodsModel;
use app\admin\validate\Goods as GoodsValidate;

/**
 * Goods
 */
class Goods extends Base
{
    public function initialize()
    {
        parent::initialize();
        $this->model = new GoodsModel();
        $this->validate = new GoodsValidate();
    }

}
