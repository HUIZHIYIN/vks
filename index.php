<!DOCTYPE html>
<html >
<head>
  <title>Huizhi Test</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
</head>
<body>

<div class="jumbotron text-center">
  <p>HUIZHI Demo</p> 
</div>



<!-- JSON Read section -->
<hr>
<div class="container">
  <div class="row">
    <div class = "col-sm-6">
      <h3>JSON files</h3>
      <p>Function: Read bunch of json files and decode</p>
      <form id="jsonRead" class="form-horizontal" method='post' action="json_read.php">
        
        <div class="form-group"> 
            <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-default">Run</button>
            <button type="button" class="btn btn-default" id="jsonReadClear">Clear</button>
            </div>
        </div>
      </form>
    </div>
    <div class = "col-sm-6" >
      <div class="panel panel-default">
        <div class="panel-heading">JSON Read Result: </div>
        <div class="panel-body" id="jsonReadResult"> </div>
       </div>
    </div>
  </div>
</div>

<!-- SQL Query -->
<hr>
<div class="container">
  <div class="row">
    <div class = "col-sm-6">
      <h3>Mysql Query</h3>
      <form id="countryQuery" class="form-horizontal" method='post' action="country_query.php">
        <div class="form-group">
            <label class="control-label col-sm-2" for="country">Country:</label>
            <select class="js-data-example-ajax" id = 'country' style="width:200px"></select>
        </div>

        <div class="form-group"> 
            <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-default">Submit</button>
            <button type="button" class="btn btn-default" id="sqlQueryClear">Clear</button>
            </div>
        </div>
      </form>
    </div>
    <div class = "col-sm-6" >
      <div class="panel panel-default">
        <div class="panel-heading">Mysql Query Result: </div>
        <div class="panel-body" id="countryQueryResult"> </div>
       </div>
    </div>
  </div>
</div>

<!-- Content Cache section -->
<hr>
<div class="container">
  <div class="row">
    <div class = "col-sm-6">
      <h3>Content Cache</h3>
      <p>Function: saving the final output of a static file for a specific period of time instead of executing the original script.</p>
      <form id="contentCache" class="form-horizontal" method='post' action="content_cache.php">
        
        <div class="form-group"> 
            <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-default">Run</button>
            <button type="button" class="btn btn-default" id="contentCacheClear">Clear</button>
            </div>
        </div>
      </form>
    </div>
    <div class = "col-sm-6" >
      <div class="panel panel-default">
        <div class="panel-heading">Content Cache Result: </div>
        <div class="panel-body" id="contentCacheResult"> </div>
       </div>
    </div>
  </div>
</div>

</body>
</html>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<!-- <script src="assets/js/init.js"></script> -->
<script>
$(document).ready(function() {
  $('#requestForm').validate({

      rules: {
        'pwd': {
          required: true,
          minlength: 5
        },
      },

      submitHandler: function(form) {
          $.ajax({
              url: form.action,
              type: form.method,
              data: $(form).serialize(),
              success: function(response) {
                console.log(response);
                  $('#result').html(response);
              }            
          });
      }
  });

  $('#jsonRead').validate({
      submitHandler: function(form) {
          $.ajax({
              url: form.action,
              type: form.method,
              data: $(form).serialize(),
              success: function(response) {
                console.log(response);
                var resultObj = $.parseJSON(response);
                console.log(resultObj);
                var tableHTML = '<table class="table">\
                                  <thead>\
                                    <tr>\
                                      <th>Namme</th>\
                                      <th>Gender</th>\
                                      <th>Age</th>\
                                    </tr>\
                                  </thead>\
                                  <tbody>';
                $.each(resultObj, function(index, item){
                  console.log(item);
                  tableHTML += '<tr>';
                  tableHTML += '  <td>' + item.name + '</td>';
                  tableHTML += '  <td>' + item.gender + '</td>';
                  tableHTML += '  <td>' + item.age + '</td>';
                  tableHTML += '</tr>';
                })
                tableHTML += '  </tbody>\
                              </table>';
                $('#jsonReadResult').html(tableHTML);
              }            
          });
      }
  });

  $('#jsonReadClear').bind('click', function(){
    $('#jsonReadResult').html('');
  })

  $('#contentCache').validate({
      submitHandler: function(form) {
          $.ajax({
              url: form.action,
              type: form.method,
              data: $(form).serialize(),
              success: function(response) {
                console.log($.parseJSON(response));
               
                $('#contentCacheResult').html($.parseJSON(response));
              }            
          });
      }
  });

  $('#contentCacheClear').bind('click', function(){
    $('#contentCacheResult').html('');
  })


  $('.js-data-example-ajax').select2({
    ajax: {
      url: '/vks/countries.php',
      dataType: 'json',
      processResults: function (data) {
          var arr = []
          $.each(data, function (index, value) {
              arr.push({
                  id: value['id'],
                  text: value['text']
              })
          })
          return {
              results: arr
          };
      },
    }
  });


  $('#countryQuery').validate({
    submitHandler: function(form) {
      $.ajax({
          url: form.action,
          type: form.method,
          data: {'country': $("#country").select2("val")},      
          success: function(response) {
            console.log($.parseJSON(response));
            var cityList = "City List: <br/>"
            $.each($.parseJSON(response), function(index, value){
              cityList += value.Name + "<br/>";
            })
            $('#countryQueryResult').html(cityList);
          }            
      });
    }
  });

  $('#sqlQueryClear').bind('click', function(){
    $('#countryQueryResult').html('');
  })
  
});
</script>
