<?php
    $address = adddress_to_string(
        array(
            'street' => isset($street_address)?$street_address: NULL,
            'city' => isset($city)?$city: NULL,
            'state' => isset($state)?$state:NULL
        )
    );
?>

<tr id="<?= $id ?>">
    <td>
        <a href="<?= base_url('evaluations/'. $position . '/' . $id); ?>"><?= isset($business_name)?$business_name:(isset($address)?$address: 'Name not provided') ?></a>
    </td>
    <td>
        <?= isset($client_first_name)? $client_first_name : '';  ?> <?= isset($client_last_name)? $client_last_name : '';  ?><?= isset($client_company)? '(' . $client_company . ')': '';  ?>
    </td>
    <td>
        <?= $address ?>
    </td>
    <td data-sort="<?= formatDate("Ymd", $report_date)?>">
        <?= isset($report_date)?formatDate("n/j/Y", $report_date):'-' ?>
    </td>
    
    <td>
        <a href="<?= base_url('evaluations/'. $position . '/' . $id); ?>"> <i class="material-icons">remove_red_eye</i></a>
        
        <form action="#" method="post"><input type="hidden" name="" value=""><button data-bovid="<?= $id; ?>" type="submit" class="delete delete-bov tooltipped" data-position="bottom" data-delay="50" data-tooltip="Delete Evaluation"><i class="delete properties material-icons">delete</i></button></form>

        <?php if($dompdf = false): ?>
        <?php echo form_open( base_url('wggenerator/download/' . $id), array('class' => 'fake-anchor-form')); ?>
            <button type="submit"> <i class="material-icons red-text">picture_as_pdf</i></button>
        <?php echo form_close(); ?>
        <?php else: ?>
            <a target="_blank" href="<?= base_url('wggenerator/report/'.$id.''); ?>"> <i class="material-icons red-text">picture_as_pdf</i></a>
        <?php endif; ?>
    </td>
</tr>
