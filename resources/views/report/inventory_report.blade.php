<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Table</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .row-span {
            font-size: 15px;
            color: #555;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <div class="team-info">
            <h3 style="text-align: center; font-size: 26px;">Inventory Report</h3>
            <p style="text-align: center">{{ $reportDate }}</p>
            @foreach($team as $teams)
                <p style="text-align: right; margin: 0;">{{ $teams->name }}</p>
                <p style="text-align: right; margin: 0;">{{ $teams->address }}</p>
            @endforeach
        </div>
        <hr>

    <h2 style="text-align: center">Drone</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Type</th>
                <th>Brand</th>
                <th>Model</th>
                <th>ID Legal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($drone as $drones)
                <tr>
                    <td>{{ $drones->name }}</td>
                    <td>{{ $drones->status }}</td>
                    <td>{{ $drones->type }}</td>
                    <td>{{ $drones->brand }}</td>
                    <td>{{ $drones->model }}</td>
                    <td>{{ $drones->idlegal }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="row-span">
                        <strong>Drone Geometry:</strong> {{ $drones->geometry }} &nbsp;&nbsp;
                        <strong>Owner:</strong> {{ $drones->users->name }}
                        <br>
                        <strong>Initial Flight Count:</strong> {{ $drones->flight_c }} &nbsp;&nbsp; 
                        <strong>Inventory/Asset:</strong> {{ $drones->inventory_asset }}
                        <br>
                        <strong>Description:</strong> {{ $drones->description }}
                    </td>
                </tr>
                @endforeach
        </tbody>
    </table>

<h2 style="text-align: center">Battery</h2>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Model</th>
            <th>For Drone</th>
            <th>Purchase Date</th>
            <th>Life Span</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($battery as $batteries)
            <tr>
                <td>{{ $batteries->name }}</td>
                <td>{{ $batteries->model }}</td>
                <td>{{ $batteries->drone->name }}</td>
                <td>{{ $batteries->purchase_date }}</td>
                <td>{{ $batteries->life_span }}</td>
            </tr>
            <tr>
                <td colspan="5" class="row-span">
                    <strong>Status:</strong> {{ $batteries->status }} &nbsp;&nbsp; 
                    <strong>Owner:</strong> {{ $batteries->users->name }} &nbsp;&nbsp; 
                    <strong>Inventory/Asset number:</strong> {{ $batteries->asset_inventory }}
                    <br>
                    <strong>Description:</strong> {{ $batteries->description }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2 style="text-align: center">Equipment</h2>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Model</th>
            <th>Type</th>
            <th>Purchase Date</th>
            <th>For Drone</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($equipment as $equipments)
            <tr>
                <td>{{ $equipments->name }}</td>
                <td>{{ $equipments->model }}</td>
                <td>{{ $equipments->type }}</td>
                <td>{{ $equipments->purchase_date }}</td>
                <td>{{ $equipments->drones->name }}</td>
            </tr>
            <tr>
                <td colspan="5" class="row-span">
                    <strong>Status:</strong> {{ $equipments->status }} &nbsp;&nbsp; 
                    <strong>Owner:</strong> {{ $equipments->users->name }} 
                    <br>
                    <strong>Insurable Value:</strong> {{ $equipments->insurable_value }} &nbsp;&nbsp;
                    <strong>Inventory/Asset number:</strong> {{ $equipments->asset_inventory }}
                    <br>
                    <strong>Description:</strong> {{ $equipments->description }} &nbsp;&nbsp;
                    
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
