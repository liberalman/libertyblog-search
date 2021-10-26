# libertyblog-search

```
ln -s /data/www/libertyblog-search/sdk/php/app/libertyblog.ini /data/www/libertyblog-search/libertyblog.ini 
```

重建索引，慎用，我们的网站已经很大了，重建一次好数小时
```
http://localhost:12030/libertyblog-search/api.php?cmd=reindex
```
添加索引
```
curl -X POST -d 'cmd=add&_id=123456&title=not mydark&content=this is not mydark&description=aaaa' localhost:12030/libertyblog/api.php
```
更新索引
```
curl -X POST -d 'cmd=update&_id=123456&title=mydark&content=this is mydark&description=dddd' localhost:12030/libertyblog/api.php
```
删除索引
```
curl -X DELETE localhost:12030/libertyblog/api.php/123456
```


