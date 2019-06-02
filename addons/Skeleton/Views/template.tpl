<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
{foreach $meta as $name => $content}
    <meta {if $content[1]}http-equiv="{$name}" {else}name="{$name}" {/if}content="{$content.0}"/>
{/foreach}
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
<body>
    {event name="before_body"}
    <nav class="navbar navbar-inverse navbar-fixed-top">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/">{$sitename}</a>
            </div>
            <div id="navbar" class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li {if $currentPath === '/'}class="active"{/if}><a href="/">Home</a></li>
                    <li {if $currentPath === '/about'}class="active"{/if}><a href="/about">About</a></li>
                    <li {if $currentPath === '/contact'}class="active"{/if}><a href="/contact">Contact</a></li>
                    <li><a href="/dashboard">Dashboard</a></li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>
    {$content}
    {event name="after_body"}
</body>
</html>