<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="icon" href="/favicon.ico" />
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css?v=1.1" rel="stylesheet" type="text/css" />
    <link href="//fonts.googleapis.com/css?family=Open+Sans:300,400,400italic,600,700?v=1.1" rel="stylesheet" type="text/css" />
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css?v=1.1" rel="stylesheet" type="text/css" />
    <link href="/assets/installer/css/bootstrap.theme.css?v=1.1" rel="stylesheet" type="text/css" />
    <link href="/assets/installer/css/install.css?v=1.1" rel="stylesheet" type="text/css" />

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js?v=1.1" type="text/javascript"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js?v=1.1" type="text/javascript"></script>
</head>
<body>
<div class="container top-offset">
    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
            <div class="block">
                <div class="block-header text-center">
                    <h1><?php echo $h1; ?></h1>
                    <h4>Garden framework</h4>
                </div>
                <hr>
                <?php echo $content;?>
            </div>
        </div>
    </div>
</div>
</body>
</html>