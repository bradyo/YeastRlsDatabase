<?php include_partial('header') ?>


<div style="margin:1em 0">
  <a class="add" href="<?php echo url_for('rlsExperiment/new') ?>"
    >New Experiment</a>
</div>


<div style="padding: 0.5em 0">
    <form action="" method="get">
        <label for="facility">Facility:</label>
        <?php $w = new sfWidgetFormChoice(array(
            'choices' => array(
                'all' => 'All Facilities',
                'Kaeberlein Lab' => 'Kaeberlein Lab',
                'Kennedy Lab' => 'Kennedy Lab',
                'GDMC' => 'GDMC',
      			)
				)) ?>
        <?php echo $w->render('facility', $sf_request->getParameter('facility', 'Kaeberlein Lab')) ?>

        <label for="status">Status:</label>
        <?php $w = new sfWidgetFormChoice(array(
            'choices' => array(
                'all' => 'Any Status',
                'pending' => 'Pending',
                'accepted' => 'Accepted',
                'rejected' => 'Rejected',
                'completed' => 'Completed',
      			)
				)) ?>
        <?php echo $w->render('status', $sf_request->getParameter('status', 'completed')) ?>
            
        <input type="submit" value="Show" />
    </form>
</div>

<table class="dataTable">
  <thead>
    <tr>
      <th>status</th>
      <th>facility</th>
      <th>number</th>
      <th>description</th>
      <th>requestor</th>
      <th>requested</th>
      <th>reviewed</th>
      <th>view</th>
    </tr>
  </thead>

  <?php foreach ($experiments as $experiment): ?>
    <tr>
      <td><?php echo $experiment['status'] ?></td>
      <td style="width:9em"><?php echo $experiment['facility'] ?></td>
      <td><?php echo $experiment['number'] ?></td>
      <td><?php echo $experiment['description'] ?></td>
      <td><?php echo $experiment['requested_by'] ?></td>
      <td><?php echo $experiment['requested_at'] ?></td>
      <td><?php echo $experiment['reviewed_at'] ?></td>
      <td>
        <a href="<?php echo url_for('rlsExperiment/show') . '?id='.$experiment['id'] ?>"
           >view</a>
      </td>
    </tr>
  <?php endforeach ?>
</table>
