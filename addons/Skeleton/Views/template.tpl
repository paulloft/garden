<!DOCTYPE html>
<html>
<head>
    {head}
    <link rel="icon" href="/favicon.ico" />
    {css}
    {javascript}
    {event name="afterHead"}
</head>
<body>
    {event name="beforeBody"}
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
                    <li class="active"><a href="/">Home</a></li>
                    <li><a href="/about/">About</a></li>
                    <li><a href="/contact">Contact</a></li>
                    <li><a href="/dashboard">Dashboard</a></li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </nav>
    {content}
    {event name="afterBody"}
</body>
</html>