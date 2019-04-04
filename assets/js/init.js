
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
                                      <th>Firstname</th>\
                                      <th>Lastname</th>\
                                      <th>Email</th>\
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
