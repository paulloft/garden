<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
{foreach $meta as $name => $content}
    <meta {if $content[1]}http-equiv="{$name}" {else}name="{$name}" {/if}content="{$content.0}"/>
{/foreach}
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <link rel="icon" href="/favicon.ico"/>
{foreach $css as $id => $src}
    <link href="{$src}" rel="stylesheet" type="text/css" id="{$id}"/>
{/foreach}
{foreach $js as $id => $src}
    <script src="{$src}" type="text/javascript" id="{$id}"></script>
{/foreach}
    {event name="after_head"}
</head>
<body class="{$addon}_addon {$controller}_controller {$action}_action">
{event name="before_body"}
{$content}
{event name="after_body"}
</body>
</html>