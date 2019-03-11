<!DOCTYPE html>
<html>
<head>
    <?php
    $meta = val('meta', $gdn);
    $separator = \Garden\Config::get('main.titleSeparator', '-');

    echo '<title>'.strip_tags($title.' '.$separator.' Garden framework')."</title>\n".(empty($meta) ? null : "    ");

    if(!empty($meta)){
        $c = count($meta);
        $i = 0;
        foreach ($meta as $name => $value) {
            $i++;
            list($content, $http_equiv) = $value;
            echo '<meta '.($http_equiv ? 'http-equiv' : 'name').'="'.$name.'" content="'.$content.'" />'.($i == $c ? null : "\n    ");
        }
    }
    ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="icon" href="/favicon.ico" />
    <?php
    $css = val('css', $gdn);
    $js = val('js', $gdn);


    if(!empty($css)) {
        $c = count($css);
        $i = 0;
        foreach ($css as $id=>$src) {
            $i++;
            echo  '<link href="'.$src.'?v='.APP_VERSION.'" rel="stylesheet" type="text/css" id="'.$id.'" />'.($i == $c ? null : "\n    ");
        }
    }?>

    <?php
    if(!empty($js)) {
        $c = count($js);
        $i = 0;
        foreach ($js as $id=>$src) {
            $i++;
            echo '<script src="'.$src.'?v='.APP_VERSION.'" type="text/javascript" id="'.$id.'"></script>'.($i == $c ? null : "\n    ");
        }
    }
    ?>
</head>
<body>
<div class="container top-offset">
    <div class="row">
        <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
            <div class="block">
                <div class="block-header text-center">
                    <h1><?php echo $title; ?></h1>
                    <h4>Garden framework</h4>
                </div>
                <hr>
                <?php echo $gdn['content'];?>
            </div>
        </div>
    </div>
</div>
</body>
</html>