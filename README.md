# libertyblog-search

```
ln -s /opt/lampp/htdocs/libertyblog-search/libertyblog.ini /usr/local/xunsearch/sdk/php/app/libertyblog.ini
```

重建索引，浏览器中直接访问如下地址
```
http://localhost:8080/libertyblog-search/api.php?cmd=reindex
```
添加索引
```
curl -X POST -d 'cmd=add&_id=123456&title=not mydark&content=this is not mydark&description=aaaa' localhost:8080/libertyblog/api.php
```
更新索引
```
curl -X POST -d 'cmd=update&_id=123456&title=mydark&content=this is mydark&description=dddd' localhost:8080/libertyblog/api.php
```
删除索引
```
curl -X DELETE localhost:8080/libertyblog/api.php/123456
```


