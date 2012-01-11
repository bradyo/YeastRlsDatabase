<?php


class UserTable extends Doctrine_Table
{
    
    public static function getInstance()
    {
        return Doctrine_Core::getTable('User');
    }

  public static function getEmails($usernames)
  {
    $db = Doctrine_Manager::getInstance()->getConnection('core')->getDbh();
    $qs = join(', ', array_fill(0, count($usernames), '?'));
    $stmt = $db->prepare('
      SELECT email FROM user WHERE username IN (' . $qs . ')
      ');
    $stmt->execute($usernames);

    $emails = array();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
      $emails[] = $row['email'];
    }

    // add developer for testing
    $emails[] = 'bradyo@uw.edu';
    return $emails;
  }

  public static function getRlsExperimentManager($facility)
  {
    $manager = null;
    if ($facility == 'Kaeberlein Lab') {
      $manager = sfConfig::get('app_rlsExperiment_kaeberleinLab_manager');
    }
    elseif ($facility == 'Kennedy Lab') {
      $manager = sfConfig::get('app_rlsExperiment_kennedyLab_manager');
    }
    elseif ($facility == 'GDMC') {
      $manager = sfConfig::get('app_rlsExperiment_gdmc_manager');
    }
    return $manager;
  }

  public static function getRlsExperimentExecutive($facility)
  {
    $executive = null;
    if ($facility == 'Kaeberlein Lab') {
      $executive = sfConfig::get('app_rlsExperiment_kaeberleinLab_executive');
    }
    elseif ($facility == 'Kennedy Lab') {
      $executive = sfConfig::get('app_rlsExperiment_kennedyLab_executive');
    }
    elseif ($facility == 'GDMC') {
      $executive = sfConfig::get('app_rlsExperiment_gdmc_executive');
    }
    return $executive;
  }

}
