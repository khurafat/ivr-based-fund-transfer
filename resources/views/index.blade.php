<!DOCTYPE html>
<html>
<head>
	<title>Users</title>
	<link rel="stylesheet" href="/style.css" type="text/css">
</head>

<body>
  <!--for demo wrap-->
  <h1>Users List</h1>
  <div>
    <table class="container">
      <thead>
        <tr>
          <th>S.no</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Mobile</th>
          <th>Balance</th>
          <th>Language</th>
        </tr>
      </thead>
  </div>
  <div>
      <tbody>
      @foreach ($customers as $customer)

        <tr>
          <td><b>{{customer->id}}</b></td>
          <td><b>{{customer->first_name}}</b></td>
          <td><b>{{customer->last_name}}</b></td>
          <td><b>{{customer->number}}</b></td>
          <td><b>{{customer->balance}}</b></td>
          <td><b>{{customer->language}}</b></td>
        </tr>
        @endforeach
      </tbody>

    </table>
  </div>



<!-- follow me template -->
<script type="text/javascript" src="/script.js"></script>
</body>
</html>