<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Document</title>
</head>
<body>
  <div class="gs-main-error">
    <div class="gs-main-error-content">
      <div class="title">Oops！您访问的页面找不到了 ~</div>
      <a class="link-btn" href="/">返回首页</a>
    </div>
  </div>
</body>
</html>

<style>
  body {
    margin: 0px;
  }
  .gs-main-error {
    width: 100%;
    padding-top: 194px;
  }
  .gs-main-error .gs-main-error-content {
    width: 100%;
    height: 609px;
    position: relative;
    background:url('/resources/images/404.png') no-repeat;
  }
  .gs-main-error-content .title {
    display: inline-block;
    position: absolute;
    font-size: 36px;
    color: #333333;
    line-height: 50px;
    top: 16px;
    left: 50%;
    transform:translateX(-50%);
  }
  .gs-main-error-content .link-btn {
    position: absolute;
    display: block;
    line-height: 50px;
    width: 180px;
    text-align: center;
    border-radius: 25px;
    background-color: #1E9FFF;
    font-size:18px;
    color: #ffffff; 
    top: 96px;
    left: 50%;
    transform:translateX(-50%);
    text-decoration: none;
  }
</style>