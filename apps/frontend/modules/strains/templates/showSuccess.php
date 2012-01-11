<?php include_partial('header') ?>

<h2>Yeast Strain Details (ID = <?php echo $strain['id'] ?>)</h2>

<table class="view">
  <tbody>
    <tr>
      <th>Strain:</th>
      <td><?php echo $strain['name'] ?></td>
    </tr>
    <tr>
      <th>Background:</th>
      <td><?php echo $strain['background'] ?></td>
    </tr>
    <tr>
      <th>Mating type:</th>
      <td><?php echo $strain['mating_type'] ?></td>
    </tr>
    <tr>
      <th>Genotype:</th>
      <td><?php echo $strain['genotype'] ?></td>
    </tr>
    <tr>
      <th>Genotype (Short):</th>
      <td><?php echo $strain['genotype_short'] ?></td>
    </tr>
    <tr>
      <th>Genotype (Pooling):</th>
      <td><?php echo $strain['genotype_unique'] ?></td>
    </tr>
    <tr>
      <th>Freezer code:</th>
      <td><?php echo $strain['freezer_code'] ?></td>
    </tr>
    <tr>
      <th>Comment:</th>
      <td><?php echo $strain['comment'] ?></td>
    </tr>

    <tr>
      <th>Created at:</th>
      <td><?php echo $strain['created_at'] ?></td>
    </tr>
    <tr>
      <th>Updated at:</th>
      <td><?php echo $strain['updated_at'] ?></td>
    </tr>

    <tr>
      <th>Is Locked:</th>
      <td>
        <?php if ($strain['is_locked']): ?>
          True
        <?php endif ?>
      </td>
    </tr>
  </tbody>
</table>


<h2>Contact Information:</h2>

<?php if (!empty($strain['owner'])): ?>
  <table class="view">
    <tbody>
      <tr>
        <th>Username:</th>
        <td><?php echo $strain['owner'] ?></td>
      </tr>
      <tr>
        <th>E-mail:</th>
        <td><?php echo $strain['email'] ?></td>
      </tr>
      <tr>
        <th>Lab:</th>
        <td><?php echo $strain['lab'] ?></td>
      </tr>
      <tr>
        <th>Location:</th>
        <td><?php echo $strain['location'] ?></td>
      </tr>
      <tr>
        <th>Phone:</th>
        <td><?php echo $strain['phone'] ?></td>
      </tr>
    </tbody>
  </table>
<?php else: ?>
  <p>Not available</p>
<?php endif ?>