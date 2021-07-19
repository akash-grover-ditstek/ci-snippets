<div class="container edit">
    <h1><?= $client->getName(); ?></h1>
        <p class="pad-left-20">Edit this client. Settings here will automatically be applied to every evaluation created for this event. <a href="#" class="hide">View all Evaluations</a></p>
    <hr>

    <?= form_open(base_url('clients/submit'), null, array('id' => $client->getId())); ?>
        <?= $this->load->view('clients/_partials/_form.php', array('client' => $client), true);  ?>
    <?= form_close(); ?>
</div>
