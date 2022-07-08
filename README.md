# yulinzhihou_api 后台接口脚手架

> 基于 `thinkphp 6.0.12LTS`
> 
> 运行环境要求PHP7.2+，兼容PHP8.1
> 
> 开发环境
> 
> OS: MAC OS Monterey 12.4
> 
> PHP: PHP 8.0.18 (cli) (built: Apr 14 2022 13:39:57) ( NTS )
> 
> Nginx: nginx/1.21.6
> 
> Mysql: Server version: 8.0.29
> 
> 


## 集成功能
- 数据迁移功能
- 登录验证 `JWT` 
- 生成 `RSA` 证书
- 异步队列执行
- 

## 部署
- 第一步：下载或者克隆代码
```bash
git clone https://github.com/yulinzhihou/yulinzhihou_api.git
```
或者
```bash
git clone https://gitee.com/yulinzhihou/yulinzhihou_api.git
```
- 第二步：安装依赖
```shell
cd yulinzhihou_api && composer install
```
- 第三步：复制 `.env.sample` 为 `.env` 并创建一个指定的数据库。配置好 `mysql` , `redis` 相关配置
会初始化数据表以及基础数据，admin,menu,role表里面
```bash
# 进入项目目录执行
cd yulinzhihou_api 
php think migrate:run
php think seed:run
```

- 第四步：