<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <title>Yeast Replicative Lifespan Database</title>
        <?php include_javascripts() ?>   
        <?php include_stylesheets() ?>
        <link rel="icon" type="image/png" href="<?php echo sfConfig::get('sf_relative_url_root') ?>/favicon.png" />
    </head>

    <body>
        <div id="top-bar">
            <div id="global-crumb">
                <a href="http://sageweb.org/">Sageweb.org</a>
                &gt; Tools
            </div>
            <div id="user-menu">
                <?php echo link_to('Submit Data', 'default/index') ?>
            </div>
        </div>

        <div id="header">
            <div id="header-text"><a href="<?php echo sfConfig::get('sf_relative_url_root') 
                    ?>">Yeast Replicative Lifespan Database</a></div>
            <div id="header-menu">
                <ul>
                    <li><?php echo link_to('Official Data', 'default/index') ?></li>
                    <li><?php echo link_to('Extended Data', 'default/index') ?></li>
                    <li><?php echo link_to('Strains', 'default/index') ?></li>
                    <li><?php echo link_to('Help', 'default/index') ?></li>
                </ul>
            </div>
        </div>
        
        <div id="main">
            <?php echo $sf_content ?>
        </div>

        <div id="footer">
            <p>Cite this database:<br />
                Olsen B, Kaeberlein M (<?php echo date('Y') ?>). 
                Yeast Replicative Lifespan Database: 
                http://sageweb.org/yeast-rls</p>
        </div>
    </body>
</html>

