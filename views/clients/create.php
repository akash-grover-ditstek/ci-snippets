<div class="container create">
    <h1>Create Client</h1>
    <hr/>
    <?php echo form_open(base_url('clients/submit'), array('onsubmit' => 'return set_indexes();')); ?>
        <?php  $this->load->view('clients/_partials/_form.php', array('client' => $client)); ?>
    <?php echo form_close(); ?>
</div>
