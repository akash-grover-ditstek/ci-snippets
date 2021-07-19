<div class="container">
    <h1>Client View</h1>
    <hr/>
    <table id="table" style="width:100%">
        <tr>
            <th>Name:</th>
            <td><?= $client->getName(); ?></td>
        </tr>
        <tr>
            <th>Company:</th>
            <td><?= $client->getCompany(); ?></td>
        </tr>
        <tr>
            <th>Phone Number:</th>
            <td><?= $client->getPhoneNumber(); ?></td>
        </tr>
        <tr>
            <th>E-mail Address:</th>
            <td><?= $client->getEmailAddress(); ?></td>
        </tr>
    </table>
</div>
