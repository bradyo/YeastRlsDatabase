<?php use_helper('UrlFilter') ?>

<style type="text/css">
	#rlsLogTable ul {
		margin: 0;
		padding: 0;
	}
	#rlsLogTable li {
		margin-left: 1.2em;
	}
</style>

<?php include_partial('header', array('buildDate' => $buildDate)) ?>

<h2>Build Log</h2>

<table id="rlsLogTable" class="dataTable">
  <thead>
    <tr>
      <th>Meta Field</th>
      <th>Value</th>
    </tr>
  </thead>
  <?php foreach ($metaData as $row): ?>
    <tr>
      <td><?php echo $row['name'] ?></td>
      <td><?php echo $row['value'] ?></td>
    </tr>
  <?php endforeach ?>
</table>
<br />

<?php if (count($rows) > 0): ?>
  <table id="rlsLogTable" class="dataTable" style="width:100%">
    <thead>
      <tr>
        <th>Experiment</th>
        <th>Build Messages</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $filename => $messages): ?>
        <tr>
          <td style="width:8em">
            <?php echo $filename ?>
          </td>
          <td>
            <ul>
              <?php foreach ($messages as $message): ?>
								<?php if (!empty($message)): ?>
	                <li><?php echo $message ?></li>
								<?php endif ?>
              <?php endforeach ?>
            </ul>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p>No results to show</p>
<?php endif ?>

