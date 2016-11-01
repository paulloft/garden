<!DOCTYPE html>
<html>
<head>
    {head}
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="icon" href="/favicon.ico" />
    {css}
    {javascript}
    {event name="afterHead"}
</head>
<body class="{$gdn.addon}_addon {$gdn.controller}_controller {$gdn.action}_action">
    {event name="beforeBody"}
    {content}
    {event name="afterBody"}
</body>
</html>