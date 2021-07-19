<?= form_input(array(
    'name' => 'user_id',
    'type' => 'hidden',
    'value' => isset($user_id) ? $user_id : $_SESSION['user_id']
)); ?>
<?= form_input(array(
    'name' => 'redirect',
    'type' => 'hidden',
    'value' => isset($_GET["redirect"]) ? $_GET["redirect"] : ''
)); ?>
<?= form_input(array(
    'name' => 'action',
    'type' => 'hidden',
    'value' => isset($_GET["action"]) ? $_GET["action"] : ''
)); ?>

<div class="contact_wrapper form_wrapper">
    <h2>Client</h2>
    <p class="section-description">Specify the business information. This will be used for reference, and to email evaluations when completed.</p>
    <br/>
    <div class="row">
        <div class="input-field col s4 required">
            <?= form_label('First Name') ?>
            <?= form_input(array(
                'name' => 'first_name',
                'id' => 'first_name',
                'value' => $client->getFirstName(),
                'required' => 'required'
            )); ?>
        </div>
        <div class="input-field col s4 required">
            <?= form_label('Last Name') ?>
            <?= form_input(array(
                'name' => 'last_name',
                'id' => 'last_name',
                'value' => $client->getLastName(),
                'required' => 'required'
            )); ?>
        </div>
        <div class="input-field col s4">
            <?= form_label('Title (Mr.,Mrs.,Ms.)') ?>
            <?= form_input(array(
                'name' => 'title',
                'id' => 'title',
                'value' => $client->getTitle()
            )); ?>
        </div>
    </div>

    <div class="row">
        <div class="input-field col s4">
            <?= form_label('Company') ?>
            <?= form_input(array(
                'name' => 'company',
                'id' => 'company',
                'class' => 'google-listing',
                'value' => $client->getCompany(),
                'placeholder' => ""
            )); ?>
            <span class="google"></span>

            <?= form_input(array(
                'name' => 'place_id',
                'id' => 'place_id',
                'type' => 'hidden',
                'value' => $client->getPlaceId()
            )); ?>

        </div>
        <div class="input-field col s4">
            <?= form_label('Email Address') ?>
            <?= form_input(array('name' => 'email_address', 'value' => $client->getEmailAddress())); ?>
        </div>
        <div class="input-field col s4">
            <?= form_label('Phone Number') ?>
            <?= form_input(array(
                'maxlength'=>'14',
                'name' => 'phone_number',
                'id' => 'phone_number',
                'class' => 'phone_number',
                'value' => $client->getPhoneNumber()
            )); ?>
        </div>
    </div>

    <div class="row">
        <div class="input-field col s3">
            <?= form_label('Street Address') ?>
            <?= form_input(array(
                'name' => 'street_address',
                'id' => 'street_address',
                'value' => $client->getStreetAddress(),
                'placeholder' => ""
            )); ?>
        </div>
        <div class="input-field col s3">
            <?= form_label('City') ?>
            <?= form_input(array(
                'name' => 'city',
                'id' => 'city',
                'value' => $client->getCity(),
                'placeholder' => ""
            )); ?>
        </div>
        <div class="input-field col s3">
            <?= mat_select(
                    'State',
                    'state',
                    get_states(),
                    $client->getState(),
                    'name = "state" id="state" tab-index=-1'
            ); ?>
        </div>
        <div class="input-field col s3">
            <?= form_label('Zipcode') ?>
            <?= form_input(array(
                'maxlength' => '5',
                'class' => 'zipcode',
                'name' => 'zipcode',
                'id' => 'zipcode',
                'value' => $client->getZipcode(),
                'placeholder' => ""
            )); ?>
        </div>
    </div>
</div>

<?php if (is_any_granted([UserRole::ROLE_DEV, UserRole::ROLE_SUPER_ADMINISTRATOR])): ?>
    <div class="save_wrapper">
        <h2>Account</h2>
        <p class="section-description">Specify the Project CRE's account this company belongs to. This field is set automatically upon user creation and shouldn't be changed regularly.</p>
        <div class="name">
            <div class="input-field">
                <?= form_dropdown("account_id", get_accounts(),
                    $client->getAccountId() ? $client->getAccountId() : get_logged_user_account(),
                    'class="account"'); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="save_wrapper">

    <div class="input-field">
        <?= form_button(array('type' => 'submit', 'class' => 'btn btn-primary', 'content' => 'Save Client')) ?>
    </div>
</div>

<script>
    function initAutocomplete() {
        Client.init();
    }
</script>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBNuq_P-RYH4b6sS_8suJRt-1glUudOIa0&libraries=places&callback=initAutocomplete"
        async defer></script>
