<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Core Resources</title>
    <?php include_javascripts() ?>   
    <?php include_stylesheets() ?>
    <link rel="icon" type="image/png" href="<?php echo sfConfig::get('sf_relative_url_root')
      ?>/favicon.png" />
  </head>
  
<body>
  <div id="viewport">
    <div id="header">
      <a href="http://www.kaeberleinlab.org">KaeberleinLab.org</a>
      > Core Resources
    </div>

    <div id="navigation" style="margin-bottom: 1em; margin:0; padding: 0">
      <div style="float:left; margin-left: 1em; font-size:16px">
        <ul class="nav">
          <li>
             <a href="<?php echo url_for('default/index') ?>">Home</a>
          </li>

          <li>Databases
            <ul>
              <li><?php echo link_to('Yeast RLS', 'yeast-rls/index') ?></li>
              <li><?php echo link_to('Yeast CLS', 'yeast-cls/index') ?></li>
              <li><?php echo link_to('TSAA', 'tsaa/index') ?></li>
              <li><a href="http://kaeberleinlab.org/dataSetManager">Dataset Manager</a></li>
            </ul>
          </li>

          <li>Strains
            <ul>
              <li><?php echo link_to('Yeast', 'yeast/index') ?></li>
              <li><?php echo link_to('Worm', 'worm/index') ?></li>
              <li><?php echo link_to('Mouse', 'mouse/index') ?></li>
            </ul>
          </li>

          <li>Resources
            <ul>
              <li><?php echo link_to('Reagents', 'reagents/index') ?></li>
              <li><?php echo link_to('Equipment', 'equipment/index') ?></li>
              <li><?php echo link_to('Skills', 'skills/index') ?></li>
            </ul>
          </li>

          <li class="last">Workflows
            <ul>
              <li><?php echo link_to('Yeast RLS Experiments', 'rlsExperiment/index') ?></li>
            </ul>
          </li>

        </ul>
      </div>


      <div id="userMenu" style="margin-right: 2px;">
        <ul>
          <?php if (!$sf_user->isAuthenticated()): ?>
            <?php echo link_to('Login', 'auth/login') ?>
          <?php else: ?>
            
            Logged in as: <?php echo $sf_user->getAttribute('username') ?>&nbsp;
            <?php if ($sf_user->hasCredential('manage users')): ?>
              <?php echo link_to('Create Account', 'account/create') ?>&nbsp;
            <?php endif ?>
            <?php echo link_to('My Account', 'account/index') ?>&nbsp;
            <a href="http://tracker.kaeberleinlab.org/login_select_proj_page.php?ref=bug_report_page.php"
               >Report Issue</a>&nbsp;
            <?php echo link_to('Logout', 'auth/logout') ?>
          <?php endif ?>
        </ul>
      </div>
    </div>

    <div style="margin:0.25em; clear: both; margin-top:1em">
      <hr />
      <?php if ($sf_user->hasFlash('errorMessage')): ?>
        <div class="errorMessage">
          <strong>Error:</strong>
          <?php echo $sf_user->getFlash('errorMessage', ESC_RAW) ?>
        </div>
      <?php endif ?>

      <?php if ($sf_user->hasFlash('warningMessage')): ?>
        <div class="warningMessage">
          <strong>Warning:</strong>
          <?php echo $sf_user->getFlash('warningMessage', ESC_RAW) ?>
        </div>
      <?php endif ?>

      <?php if ($sf_user->hasFlash('infoMessage')): ?>
        <div class="infoMessage">
          <strong>Warning:</strong>
          <?php echo $sf_user->getFlash('infoMessage', ESC_RAW) ?>
        </div>
      <?php endif ?>

      <?php if ($sf_user->hasFlash('successMessage')): ?>
        <div class="successMessage">
          <strong>Success:</strong>
          <?php echo $sf_user->getFlash('successMessage', ESC_RAW) ?>
        </div>
      <?php endif ?>

      <?php echo $sf_content ?>
    </div>

    <div id="footer">
      Kaeberlein Lab <?php echo date('Y', time()) ?>
    </div>
  </div>
</body>
</html>

