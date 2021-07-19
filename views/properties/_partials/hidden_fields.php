<?=
form_input(
    array(
        'name' => 'listing[account_id]',
        'id' => 'listing[account_id]',
        'type' => 'hidden',
        'value' => $listing->getAccountId()
    )
);
?>

<?=
form_input(
    array(
        'name' => 'listing[user_id]',
        'id' => 'listing[user_id]',
        'type' => 'hidden',
        'value' => $listing->getUserId()
    )
);
?>

<?= form_input(
    array(
        'name' => 'listing[id]',
        'id' => 'listing[id]',
        'type' => 'hidden',
        'value' => $listing->getId()
    )
);
?>

<?=
form_input(
    array(
        'name' => 'step',
        'type' => 'hidden',
        'value' => $step
    )
);
?>

<?=
form_input(
    array(
        'name' => 'current_listing_status',
        'id' => 'current_listing_status',
        'type' => 'hidden',
        'value' => $listing->getStatus()
    )
);
?>
