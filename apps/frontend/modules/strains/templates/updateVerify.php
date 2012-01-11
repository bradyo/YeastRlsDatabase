<?php include_partial('header') ?>

<h2>Update Strains</h2>

<?php if(count($errors) > 0): ?>
  <div>
    The following errors occured:
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?php echo $error ?></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<?php if(count($failedStrains) > 0): ?>
  <div>
    The following strains cannot be updated:
    <ul>
      <?php foreach ($failedStrains as $strainName => $reason): ?>
        <li><?php echo $strainName.': '.$reason ?></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>

<?php if (count($heldStrains) + count($addStrains) > 0): ?>
  <form action="<?php echo url_for('yeast/update') ?>" method="post" >

    <?php if(count($addStrains) > 0): ?>
      <div>
        The following strains will be added:
        <ul>
          <?php foreach ($addStrains as $strainName => $strain): ?>
            <li>
              <?php echo $strainName ?>
              <input type="hidden" name="action[<?php echo $strain['name'] ?>]"
                     value="add" />
              <input type="hidden" name="data[<?php echo $strain['name'] ?>]"
                     value="<?php echo $strain['data'] ?>" />
            </li>
          <?php endforeach ?>
        </ul>
      </div>
    <?php endif ?>

    <?php if(isset($heldStrains) && count($heldStrains) > 0): ?>
      <div>
        The following strains already exist and can be updated:
        <ul>
          <?php foreach ($heldStrains as $strainName => $strain): ?>
            <li>
              <?php echo $strainName ?>:
              <div style="display:inline; margin-left: 2em">
                <input type="radio" name="action[<?php echo $strain['name'] ?>]"
                       value="update" checked="checked" />update
                <input type="radio" name="action[<?php echo $strain['name'] ?>]"
                       value="skip" />skip
              </div>
              <input type="hidden" name="data[<?php echo $strain['name'] ?>]"
                     value="<?php echo $strain['data'] ?>" />
            </li>
          <?php endforeach ?>
        </ul>
      </div>
    <?php endif ?>

    <input type="submit" name="update" value="Submit" />  
  </form>

<?php endif ?>

