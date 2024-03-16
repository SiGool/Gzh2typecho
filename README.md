# Gzh2typecho - 微信公众号对话发布typecho文章
## 特性
- √ 微信公众号消息对话形式撰写文章
- √ 支持OpenID白名单
- √ 支持设置typecho文章撰写里的所有表单项（自定义字段不支持 ×）
- √ 插件配置面板支持配置各表单项进行设置的唤醒口令
  
## 安装条件
- Typecho v1.2.1 其他版本我没有安装测试过！但下方我会大概阐述下插件的一些工作原理，可以参照以适配其他Typecho版本对本插件进行修整

- PHP v7.4+，Typecho v1.2.1官方要求是PHP v7.2+，理论上你用v7.2+也可以，但可能我对 [w7corp/easywechat](https://github.com/w7corp/easywechat) 这个包进行调用的部分代码你需要调整。因为 [w7corp/easywechat](https://github.com/w7corp/easywechat) 这个包我用的是5.x，而5.x刚好需要PHP v7.4+，PHP v7.0 ~ v7.4以下，只能装 [w7corp/easywechat](https://github.com/w7corp/easywechat) 4.x版本
  
- 安装 [w7corp/easywechat](https://github.com/w7corp/easywechat) 5.x版本，如果你有composer，直接网站根目录下运行命令：
    ```shell
    composer require w7corp/easywechat:^5.30
    ```
    如果你没有用composer，你可以手动解压本仓库里的vendor.zip到网站根目录 或者 PHP版本比7.4稍低一点儿，composer装不上5.x，你也可以手动解压或者强制composer安装（【注】：这里我没有测试过PHP 7.4稍低一些版本，比如7.2，7.3这些是否运行 [w7corp/easywechat](https://github.com/w7corp/easywechat) 5.x 代码没有异常的）

## 搭建
1. Typecho能正常跑起来了
   
2. 确保给网站上了HTTPS，不上HTTPS可以回家了，后面的腾讯公众号开发不会带你玩的。
   分享下鄙人的笨方法吧：
   1. 下载 [mkcert(windows的一般就是amd那个)](https://github.com/FiloSottile/mkcert/releases) 然后看着文档运行最多两条命令就出证书了。
   2. 现在是有证的人了，咱打开小皮面板，就是以前叫phpstudy那个，给现在的这个80站点也配个443就是HTTPS的站点，其实填的信息都一样就是端口写443，然后会弹出要上传东西
      ![](https://gitee.com/sigool/sg-img-services01/raw/master/create_443_ws.png)
      ![](https://gitee.com/sigool/sg-img-services01/raw/master/80_443_ws_list.png)
      上传的时候别搞错了，名字带key的就是秘钥
   3. 要搞内网穿透，不然本地开发的项目没法暴露出去给外网访问的，麻花疼那边服务器就没法跟我们项目交互了。可以用 [cpolar](https://www.cpolar.com/) 家的，建个443的HTTP隧道，启动就完事了
      ![](https://gitee.com/sigool/sg-img-services01/raw/master/cpolar_using.png)
      
3. 下载本插件到Typecho的插件文件夹（网站根目录/usr/plugins）
   
4. 安装 [w7corp/easywechat](https://github.com/w7corp/easywechat) 5.x 依赖
   - 方法一：网站根目录下，运行composer命令：
       ```shell
       composer require w7corp/easywechat:^5.30
       ```
   - 方法二：解压本仓库的vendor.zip到网站根目录

5. 试试HTTPS能成功访问公众号要配置的服务器地址(URL)了没有：
   服务器地址(URL)为：cpolar公网地址/usr/plugins/Gzh2typecho/Server/server.php，
   比如我现在的是：https://282bdaf8.r12.cpolar.top/usr/plugins/Gzh2typecho/Server/server.php
   ![](https://gitee.com/sigool/sg-img-services01/raw/master/wx_web_url_ok.png)
   出现响应了，就说明HTTPS成功了，而且这个URL要拿去微信公众号后台配置 服务器地址(URL) 的

6. 现在要到微信公众号后台进行服务器配置   
   ![](https://gitee.com/sigool/sg-img-services01/raw/master/cf_server_info_for_wx.png)
   - URL 要填的就是刚才上一个步骤测试响应的那个URL
   - 消息加解密方式选安全模式
   - Token和EncodingAESKey要记下来，等会要到插件配置面板进行配置的
   - 填写完后提交，成功会弹出提示并且跳转回上一个页面，右边会显示红色的停用（！！！这里一定要出现红色的停用，不然是芝麻开门没对上的，后面怎么折腾都没用，这步含金量很高！！！），这时候你就成功50%了
     ![](https://gitee.com/sigool/sg-img-services01/raw/master/verfiy_server_for_wx_success.png)   

7. 配置完服务器配置，上方还有一栏 IP白名单，我们需要加入当前代码所在PC或者服务器的IP，否则文章内容无法使用图片和视频。
    如果你想了三天都没想明白IP是啥，你可以等环境搭建完成后参照下方测试发布文章，文章内容带上图片或视频。点发布链接，你不会发布成功，在 [w7corp/easywechat](https://github.com/w7corp/easywechat) 生成的日志（网站根目录/usr/plugins/Gzh2typecho/Server/logs/easywechat.log）可以找到微信拒绝你的IP

8. 登录Typecho后台，到插件管理启用本插件，启用后在最右边操作列点击设置，对本插件进行配置：
    - 填写开发者ID和密码
      ![](https://gitee.com/sigool/sg-img-services01/raw/master/appid_explain.png)
      ![](https://gitee.com/sigool/sg-img-services01/raw/master/appsecret_explain.png)
      就是对应公众号后台这里：
      ![](https://gitee.com/sigool/sg-img-services01/raw/master/appid_appsecret_where_show.png)
    - Token和EncodingAESKey
      ![](https://gitee.com/sigool/sg-img-services01/raw/master/token_explain.png)
      ![](https://gitee.com/sigool/sg-img-services01/raw/master/encodingaeskey_explain.png)
      对应就是公众号后台这里，刚刚已经叫你偷偷记下来的了
      ![](https://gitee.com/sigool/sg-img-services01/raw/master/token_aeskey_where_show.png)
    - 这四个项是必须配置的，如果没填错的话，这里就已经成功99%了（【注：保存设置后，不要随意启用禁用本插件，因为你禁用再启用后填写的这些开发者ID，密码这些都会丢失的了，所以最好自己也备份一份。备份可以备份 根目录/usr/plugins/Gzh2typecho/Server/cfg.php 这个文件，因为保存设置会写出配置文件写出的就是这个】）
    
9. 配置用于发布文章的账号uid。你可以新建一个专门的账号用于本插件发布文章，当然也可以不配置，用默认的1
   ![](https://gitee.com/sigool/sg-img-services01/raw/master/where_setting_uid_for_pub_post.png)
   
10. 到这里你已经完成环境搭建了

## 测试及一些原理说明

- 当你触发到插件配置面板设定的口令时，它会跟你互动的
  ![](https://gitee.com/sigool/sg-img-services01/raw/master/com_write_post_show.png)
  并且会缓存下你设定各文章表单项的内容，默认缓存时间在这里修改
  ![](https://gitee.com/sigool/sg-img-services01/raw/master/modify_cache_editing_post_secs.png)
  
- 那本插件的OpenID白名单如何启用？
   首先，你要获取该用户微信对应该公众号的OpenID，你可以从 [w7corp/easywechat](https://github.com/w7corp/easywechat) 生成的日志查找，当然你牛逼也可以想别的办法
   ![](https://gitee.com/sigool/sg-img-services01/raw/master/where_get_openid.png)
  拿到OpenID就可以去本插件的配置面板进行白名单配置了
  ![](https://gitee.com/sigool/sg-img-services01/raw/master/where_setting_openid.png)
  
- 菜单图片说明：当你在本插件配置面板设定了菜单图片的来源后，点击保存设置。默认会将该填写的菜单图片从远程下载到缓存目录
   当有用户跟微信公众号互动了，这时候会比对是否需要将这个缓存的图片上传到公众号后台的素材库中。
  为什么要这样做？1是在typecho的后台中直接就上传到公众号素材库中代码会很繁琐，相当于走一遍easywechat做的事了，又要搞access token之类又要做爹又要做妈。 2是等有用户跟公众号互动了，这时候为啥又要上传到素材库中，因为回复用户图片内容就是要mediaID，而mediaID就是要从素材库中获取，所以要先上传到素材库中获取mediaID然后缓存起来，下次回复用户直接就有mediaID用了.
  所以千万别手痒老是清空了公众号后台的素材库，说不定里面就有菜单图片，然后你老是测试说咋不回复菜单图片

- 输入发布口令后，做了什么？
  ![](https://gitee.com/sigool/sg-img-services01/raw/master/after_enter_post_keyword.png)
  为什么要返回一个临时链接？这是什么？首先要明白，我这个插件站的立场是同时支持个人公众号即订阅号，而个人订阅号接口是很多限制的。比如没有客服接口。传统的公众号消息开发就是，用户发一条消息给公众号，公众号就可以回复一条消息这就叫被动回复。而主动回复的意思就是，用户没发消息来我公众号都可以主动发消息给他（当然这个客服接口也有一定限制，比如多少小时内用户发过消息给公众号之类）。
  还有一点就是被动回复消息有一个致命伤就是5秒内必须应答，比如用户说 @发布|> 这个消息后，我收到了，我5秒内没应答微信早就断开等待我的响应了（有客服接口就不一样，不等就不等吧，反正我可以主动上门再发条结果给你），所以我没法在通过微信服务器告诉用户是否发布成功了，5秒可以做什么？脱裤子都不够。所以我们返回一个临时链接代替，当用户自己点击链接后，就相当于自己提交新增文章表单了，同时页面可以显示结果。
  临时链接都是固定地请求 根目录/usr/plugins/Gzh2typecho/Server/post.php 这个脚本，但是链接上的token这个query string不是固定的，它是一个随机的字符串，它指向了正在编辑地文章内容，token是有有效期的（所以说这个链接是临时的，默认10分钟内点击有效）
  ![](https://gitee.com/sigool/sg-img-services01/raw/master/pub_link_valid_secs.png)
  
- 点击临时链接，它做了啥子？
  - 首先它会看下带过来的token是否失效？如果没有失效它就成功取出要发布的文章内容了。
  - 然后从微信服务器下载文章内容中的图片和视频这些媒体文件（如果有的话）
  - 尝试以插件配置面板设置的用户进行登录。它是如何做到登录上去的？我只是设过一个账号的uid，密码啥也没给啊！原理是这样，post.php这个发布脚本它会模拟Typecho后台登录，账号是特定的“Gzh2typecho”这串标识，密码则是传过来的这个token。
    因为账号密码都是乱填的所以会登录失败（有一定几乎会登录成功！赶紧可以去买彩票了，你设定的这么巧），本插件监听了Typecho登录失败的事件，所以当登录失败后，我们比对了账号就是Gzh2typecho，密码就是有效的token这时候就自动登录成功了，然后响应结束不给继续走Typecho后面的老路了
  - 后面就是上传文章内容的媒体文件到附件（有的话），模拟填写各表单项和发布了

## 常见问题
1. 为什么文章内容使用图片或视频就不行？
    - 检查是否在微信公众号后台正确配置了IP白名单
    - 检查PHP是成功启用了fileinfo扩展
    
## 鸣谢
- [typecho](https://github.com/typecho/typecho)
- [easywechat](https://github.com/w7corp/easywechat)
- [微信公众号](https://mp.weixin.qq.com/)

## ！打广告

![](https://gitee.com/sigool/sg-img-services01/raw/master/wechat.jpg)

