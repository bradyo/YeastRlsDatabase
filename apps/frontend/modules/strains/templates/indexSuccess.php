<?php use_helper('UrlFilter') ?>
<?php use_helper('JavascriptBase') ?>
<?php use_javascript('strain.js') ?>


<?php include_partial('header') ?>

<div id="filterDiv">
  <form name="filterForm" action="<?php echo url_for('yeast/index') ?>" method="get" >
    <div>
      <table class="form">
        <tr>
          <td class="label"><label>Search:</label></td>
          <td>
            <?php echo $filterForm['search'] ?>
            <span class="note">searches all fields</span>
          </td>
        </tr>
        <tr>
          <td class="label"><label>Strain:</label></td>
          <td>
            <?php echo $filterForm['name'] ?>
            <span class="note">separate multiple values with a comma or space</span>
          </td>
        </tr>
        <tr>
          <td class="label"><label>Genotype:</label></td>
          <td>
            <?php echo $filterForm['genotype'] ?>
            <span class="note">case sensitive, separate multiple values with a comma or space</span>
          </td>
        </tr>
        <tr>
          <td class="label"><label>Background:</label></td>
          <td><?php echo $filterForm['background'] ?></td>
        </tr>
        <tr>
          <td class="label"><label>Mating Type:</label></td>
          <td><?php echo $filterForm['mating_type'] ?></td>
        </tr>
        <tr>
          <td class="label"><label>Location:</label></td>
          <td><?php echo $filterForm['location'] ?></td>
        </tr>
        <tr>
          <td class="label"><label>Sort By:</label></td>
          <td>
            <?php echo $filterForm['sort_by'] ?>
            <?php echo $filterForm['sort_order'] ?>
          </td>
        </tr>

        <tr>
          <td></td>
          <td><input type="submit" value="Apply Filter" /></td>
        </tr>
      </table>
    </div>
  </form>

  <hr />
</div>

<form name="exportForm" action="<?php echo url_for('yeast/export').'?'.url_params() ?>" method="post">

  <div style="clear: both; margin-top: 1em; text-align: center; height: 2em">
    <div style="float:right; height:2em">
      <?php if ($sf_user->isAuthenticated() && $sf_user->hasCredential('update yeast strains')): ?>
        <a href="<?php echo url_for('yeast/index').'?owner='.$sf_user->getAttribute('username') ?>">My Strains</a>&nbsp;
        <a href="<?php echo url_for('yeast/update') ?>">Update</a>
      <?php endif ?>
    </div>

    <div style="float: left; height: 2em;">
      <strong>Export:</strong>
      <select name="exportType">
        <option value="selected">Selected</option>
        <option value="all">All</option>
      </select>
      <button type="submit" name="submit" value="export">Export</button>
    </div>
  </div>

  <table class="dataTable" style="width:100%">
    <thead>
      <tr>
        <th><input type="checkbox" id="checkAll" /></th>
        <th>Strain</th>
        <th>Background</th>
        <th>Mating type</th>
        <th>Full Genotype</th>
        <th>Pooling Genotype</th>
        <th>Freezer Code</th>
        <th>E-mail</th>
        <th>Location</th>
        <th style="text-align: center">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($rows) > 0): ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td style="width:0.5em">
              <input type="checkbox" name="export[<?php echo $row['id'] ?>]" />
            </td>
            <td style="width:6em"><?php echo $row['name'] ?></td>
            <td style="width:6em"><?php echo $row['background'] ?></td>
            <td style="width:6em"><?php echo $row['mating_type'] ?></td>
            <td><?php echo $row['genotype'] ?></td>
            <td><?php echo $row['genotype_unique'] ?></td>
            <td><?php echo $row['freezer_code'] ?></td>
            <td><?php echo $row['email'] ?></td>
            <td><?php echo $row['location'] ?></td>
            <td style="text-align:center">
              <a href="<?php echo url_for('yeast/show?id='.$row['id']) ?>">
                <img src="<?php echo sfConfig::get('sf_relative_url_root') ?>/images/view.png" border="0px" alt="view" />
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr style="background-color:white;">
          <td colspan="0" style="padding:4px;">No results matching query</td>
        </tr>
      <?php endif ?>
    </tbody>
  </table>
</form>

<?php include_partial('global/paginationLite', array('pager' => $pager)) ?>
