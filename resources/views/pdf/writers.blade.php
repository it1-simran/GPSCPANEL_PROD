<?php
use App\Helper\CommonHelper;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Users List</h1>
    <table>
        <thead>
            <tr>
                <th>S.no</th>
                <th>Template Name</th>
                <th>Device Category</th>
                <th>Created at</th>
                <th>Last Edit</th>
                <th>Default Template</th>
                <th>Ip</th>
                <th>Port</th>
                <th>Sleep Interval</th>
                <th>Transmission Interval</th>
                <th>Active Status</th>
                <th>Fota</th>
                <th>Ping Interval</th>
            </tr>
        </thead>
        <tbody>
            <?php
              $i = 1;

              ?>
            @foreach ($users as $user)
            <?php
                $config = json_decode($user->configurations,true);    
            ?>
            <tr>
                <td><?php echo $i; ?></td>
                <td>{{ $user->template_name }}</td>
                <td><?php echo CommonHelper::getDeviceCategoryName($user->device_category_id); ?> </td>
                <td>{{ $user->created_at }}</td>
                <td>{{ $user->updated_at }}</td>
                <td>{{ $user->default_template == 1 ? 'yes' :'no' }}</td>
                @foreach( $config as $field => $value)

                    <td><strong><?php echo $value ?></td>
                @endforeach
                <!-- <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td> -->
                <!-- Add more columns as needed -->
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
