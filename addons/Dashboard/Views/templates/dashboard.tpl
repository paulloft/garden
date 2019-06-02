<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
{foreach $meta as $name => $content}
    <meta {if $content[1]}http-equiv="{$name}" {else}name="{$name}" {/if}content="{$content.0}"/>
{/foreach}
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0,user-scalable=no">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="icon" href="/favicon.ico" />
{foreach $css as $id => $src}
    <link href="{$src}" rel="stylesheet" type="text/css" id="{$id}"/>
{/foreach}
{foreach $js as $id => $src}
    <script src="{$src}" type="text/javascript" id="{$id}"></script>
{/foreach}
    {event name="after_head"}
</head>
<body class="{$gdn.addon}_addon {$gdn.controller}_controller {$gdn.action}_action">
{event name="before_body"}
<div class="main-wrapper">
    <div class="sidebar">
        <div class="scroll-container">
            <div class="box-wrapper">
                <div class="shortcut">
                    <div class="sitename">{$sitename}</div>
                    <div class="close" data-action="menu_close"><i class="fa fa-close"></i></div>
                </div>
                <h3>{"Navigation"|translate}</h3>
                {module name="dashboard/sidebar"}
            </div>
        </div>
    </div>
    <div class="main-block transition">
        <div class="top-menu">
            {module name="dashboard/header"}
        </div>
        <div class="content-wrapper clearfix">
            <div class="main-container" id="content">
                <h1>{$h1}</h1>
                {$content}
            </div>
        </div>
        <footer class="inner">
            Worked on <a href="https://github.com/paulloft/garden">Garden Framework</a> v{APP_VERSION}.
        </footer>
    </div>
</div>
{event name="after_body"}
</body>
</html>