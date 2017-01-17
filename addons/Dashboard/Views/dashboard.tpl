<!DOCTYPE html>
<html>
<head>
    {head}
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0,user-scalable=no">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="icon" href="/favicon.ico" />
    {css}
    {javascript}
    {event name="afterHead"}
</head>
<body class="{$gdn.addon}_addon {$gdn.controller}_controller {$gdn.action}_action">
{event name="beforeBody"}

<div class="main-wrapper">
    <div class="sidebar">
        <div class="scroll-container">
            <div class="box-wrapper">
                <div class="shortcut">
                    <div class="sitename">{c('main.sitename')}</div>
                    <div class="close" data-action="menu_close"><i class="fa fa-close"></i></div>
                </div>
                <h3>{t code="Navigation"}</h3>
                {module name="sidebar"}
            </div>
        </div>
    </div>
    <div class="main-block transition">
        <div class="top-menu">
            {module name="header"}
        </div>
        <div class="content-wrapper clearfix">
            <div class="main-container" id="content">
                <h1>{$title}</h1>
                {content}
            </div>
        </div>
        <footer class="inner">
            Worked on <a href="https://github.com/paulloft/garden">Garden Framework</a> v{APP_VERSION}.
        </footer>
    </div>
</div>
{event name="afterBody"}
</body>
</html>