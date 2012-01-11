<?php use_javascript('jquery-1.3.2.min.js') ?>
<?php use_javascript('rlsExperiment/add.js') ?>

<?php include_partial('header') ?>
<h2>
  <?php echo link_to('RLS Experiments' , 'rlsExperiment/list') ?>
  > Add Experiment
</h2>


<div class="right-sidebar" style="width:20em">
  <strong>Tips</strong>

  <p>Haploid and heterozygous didploid delection collection strains are named
    as "DC:" followed by plate location (i.e. "DC:171F4").</p>
  <br />

  <p>Homozygous deletion collection strains are named "DC_HOMDIP:" followed by
    plate location (i.e. "DC_HOMDIP:49H2").</p>
  <br />

  <?php if (count($medias) > 0): ?>
    <p>Use the following media values before creating new ones:</p>
    <div style="max-height:40em; overflow: auto">
      <ul id="mediasList">
        <?php foreach ($medias as $media): ?>
          <li><?php echo $media ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endif ?>
</div>


<div style="max-width: 62em; margin-right: 24em">

  <p style="margin: 1em 0em;">This form is for adding an experiment to the yeast
    replicative lifespan disection queue. Paste your experiment key
    (<a href="<?php echo sfConfig::get('sf_relative_url_root')
      ?>/files/yeast_rls_key_template.xls">download template</a>) into the
      "Key Data" text area below. Your experiment will be reviewed by the
      experiment manager.
  </p>

  <?php if (isset($errors)): ?>
    <div class="errorMessage" style="clear:none;">
      The experiment could not be added because of the errors listed below.
      Please correct the errors and try again:
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?php echo $error ?></li>
        <?php endforeach ?>
      </ul>
    </div>
  <?php endif ?>

  <form id="experimentForm" action="<?php echo url_for('rlsExperiment/new') ?>" method="post">
    <table class="form">
      <tr>
        <th style="vertical-align:top"><label>Facility:</label></th>
        <td>
          <?php echo $form['facility'] ?>
          <?php echo $form['facility']->renderError() ?>
        </td>
      </tr>
      <tr>
        <th style="vertical-align:top"><label>Description:</label></th>
        <td>
          <?php echo $form['description'] ?>
          <?php echo $form['description']->renderError() ?>
        </td>
      </tr>
      <tr>
        <th style="vertical-align:top"><label>Key Data:</label></th>
        <td style="vertical-align: top">
          <?php echo $form['key_data'] ?>
          <?php echo $form['key_data']->renderError() ?>
          <div id="keyWarnings"></div>
        </td>
      </tr>
      <tr>
        <th style="vertical-align:top"><label>Message:</label></th>
        <td style="vertical-align: top">
          <?php echo $form['message'] ?>
          <p>Optionally include a message for the reviewer.</p>
        </td>
      </tr>
      <tr>
        <td></td>
        <td style="padding-top:2em">
          <button type="submit" name="submit" style="height:3em; width:16em">Submit</button>
        </td>
      </tr>
    </table>
    <br />
  </form>

</div>