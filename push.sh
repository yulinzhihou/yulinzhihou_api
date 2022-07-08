#!/usr/bin/env bash
# author yulinzhihou@gmail.com
# data: 2022-07-09
# 用于上传代码到代码仓库。提前建立好分支与线上的绑定关系
# 参数1： 分支名： master
# 参数2：提交代码的信息

[ -n $1 ] && "master";
git add -A
git commit -m "$2"
git push origin "$1"