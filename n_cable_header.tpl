<!DOCTYPE html>
<html lang="hu">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-2">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="bootstrap/bootstrap.min.css">
<link rel="stylesheet" href="bootstrap/typeahead.css">
<link href="bootstrap/font-awesome/css/font-awesome.min.css" rel="stylesheet">
<link href="bootstrap/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet">
<script src="bootstrap/jquery.min.js"></script>
<script src="bootstrap/jquery.validate.min.js"></script>
<script src="bootstrap/additional-methods.min.js"></script>
<script src="bootstrap/bootstrap.min.js"></script>
<script src="bootstrap/typeahead.js"></script>
<script src="bootstrap/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="bootstrap/bootstrap-datepicker/locales/bootstrap-datepicker.hu.min.js"></script>
<link rel="stylesheet" href="bootstrap/bootstrap-select/bootstrap-select.min.css" />
<script src="bootstrap/bootstrap-select/bootstrap-select.min.js"></script>
<script src="bootstrap/bootstrap-select/defaults-hu_HU.min.js"></script>
<script src="js/n_cable_new.js"></script>
<script src="js/n_cable_new_validation.js"></script>
<script src="js/utils.js"></script>
{literal}

<style>

.input-group .bootstrap-select.form-control {
        z-index: inherit;
}

.glyphicon.fast-right-spinner {
    -webkit-animation: glyphicon-spin-r 1s infinite linear;
    animation: glyphicon-spin-r 1s infinite linear;
}

.glyphicon.normal-right-spinner {
    -webkit-animation: glyphicon-spin-r 2s infinite linear;
    animation: glyphicon-spin-r 2s infinite linear;
}

.glyphicon.slow-right-spinner {
    -webkit-animation: glyphicon-spin-r 3s infinite linear;
    animation: glyphicon-spin-r 3s infinite linear;
}

.glyphicon.fast-left-spinner {
    -webkit-animation: glyphicon-spin-l 1s infinite linear;
    animation: glyphicon-spin-l 1s infinite linear;
}

.glyphicon.normal-left-spinner {
    -webkit-animation: glyphicon-spin-l 2s infinite linear;
    animation: glyphicon-spin-l 2s infinite linear;
}

.glyphicon.slow-left-spinner {
    -webkit-animation: glyphicon-spin-l 3s infinite linear;
    animation: glyphicon-spin-l 3s infinite linear;
}

@-webkit-keyframes glyphicon-spin-r {
    0% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
    }

    100% {
        -webkit-transform: rotate(359deg);
        transform: rotate(359deg);
    }
}

@keyframes glyphicon-spin-r {
    0% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
    }

    100% {
        -webkit-transform: rotate(359deg);
        transform: rotate(359deg);
    }
}

@-webkit-keyframes glyphicon-spin-l {
    0% {
        -webkit-transform: rotate(359deg);
        transform: rotate(359deg);
    }

    100% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
    }
}

@keyframes glyphicon-spin-l {
    0% {
        -webkit-transform: rotate(359deg);
        transform: rotate(359deg);
    }

    100% {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
    }
}

</style>

<script>

  $(document).ready(function() {

    $('[data-toggle="tooltip-delete"]').tooltip({title: "Törlés", container: ".delete"}); 
    $('[data-toggle="tooltip-new"]').tooltip({title: "Új rekord", container: ".new"}); 
    $('[data-toggle="tooltip-modify"]').tooltip({title: "Módositás", container: ".modify"});
    $('[data-toggle="tooltip-cancel"]').tooltip({title: "Mégsem", container: ".cancel"});
    $('[data-toggle="tooltip-reset"]').tooltip({title: "Kijelolesek torlese", container: ".reset"});

    $('#flt_i_time_datepicker').datepicker({
	format: "yyyy/mm/dd",
	weekStart: 1,
	language: "hu",
    	clearBtn: true,
    	autoclose: true,
	todayBtn: "linked",
	todayHighlight: true,
	toggleActive: true
    });

    $('#i_time_datepicker').datepicker({
        format: "yyyy/mm/dd",
        weekStart: 1,
        language: "hu",
        clearBtn: true,
        autoclose: true,
        todayBtn: "linked",
        todayHighlight: true,
        toggleActive: true
    });

    $("#i_time_datepicker").datepicker().on('show.bs.modal', function(event) {
      // prevent datepicker from firing bootstrap modal "show.bs.modal"
      event.stopPropagation(); 
    });

    $('#flt_i_time_datepicker').on('changeDate', function() {
	if ($(this).val() === "") { 
		$('#flt_i_time').val('Összes'); 
	} else {
		$('#flt_i_time').val(
        		$('#flt_i_time_datepicker').datepicker('getFormattedDate')
    		);
	}
	$('form[name=frmFilter]').submit();
    });

    $('button[name=btnNewCable]').on('click', async function(e) {

	e.preventDefault(); // Preventing the form from being submitted ...

	const form = $(e.target);

	// in case of mass update we disable most of the controls ... they should be enabled to send their value during posting
        $('#newCableModal .form-control').prop('disabled', false);

        $('#start').val($("#c_dev_start_select").val());
        $('#end').val($("#c_dev_end_select").val());

	// add zone information to the connection points
	if ($('#s_conn_point_select').val()) {
          $('#s_conn_point').val('Z' + $('#start').val().substring(3, 6) + $("#s_conn_point_select").val());
        } else { // empty for spare cable
	  $('#s_conn_point').val('');
        } 
	if ($('#e_conn_point_select').val()) {
          $('#e_conn_point').val('Z' + $('#end').val().substring(3, 6) + $("#e_conn_point_select").val());
	} else { // empty for spare cable
	  $('#e_conn_point').val('');
	}

        $('#cable_type_id').val($("#cable_type_select").val());
        $('#cable_pair_status_id').val($("#cable_pair_status_select").val());
        $("#cable_purpose_id").val($("#cable_purpose_select").val());
       	$('#i_time').val($('#i_time_datepicker').datepicker('getFormattedDate'));

	// in case chk_group checkboxes are set then we do a mass update
	if (chk_group.length) {
	  $('<input>').attr({
	    type: 'hidden',
	    name: 'chk_group',
	    checked: true,
	    form: 'frmNewCable',
	    value: chk_group.toString()
	  }).appendTo('#frmNewCable');
	}

	var cable_comment = $('#newCableModal textarea#cable_comment').val();
	var cable_id      = $('#newCableModal input[name=cable_id]').val() 

	if (cable_id) {

	  const parameters = JSON.stringify({'cable_id': cable_id, 'cable_comment': cable_comment, 'modal': 'newCableModal'});

	  try {
    		const res = await fetch('/cables/set_cable_comments.php', {
                	method: 'POST',
			headers: { "Content-Type": "application/json" },
                	body: parameters
    		});
    		const data = await res.json();
    		if (data.status == 'error') {
      			displayError(data.error_message, '#cable_comment');
    		} else {
			form.submit();
		}
  	  } catch (e) {
    		console.log('setCableComments: ' + e);
  	  }

	}

    });

    $('#s_conn_point_select_reset').on('click', function() {

      $('#s_conn_point_select').val('');
      $('#s_conn_point_select').selectpicker('refresh');

    });

    $('#e_conn_point_select_reset').on('click', function() {

      $('#e_conn_point_select').val('');
      $('#e_conn_point_select').selectpicker('refresh');

    });

    $("#newCableModal #btnCancel").click(function() {
      $("form[name='frmNewCable']").validate().resetForm();
    });

  });

  var chk_group = new Array();

  function uncheckAll() {

    var inputs = document.getElementsByTagName('input');
    var nyul = chk_group.length;
    if (nyul == 0) {
      for (i = 0; i < inputs.length - 1; i++){
        if(inputs[i].getAttribute('type') == 'checkbox') {
          inputs[i].checked = true;
          add_cables_id(inputs[i].id);
        }
      }
    } else {
      for (i = 0; i < nyul; i++) {
        document.getElementById(chk_group[i]).checked = false;
      }
      chk_group = [];
    }

  }

  function add_cables_id(field_id) {

    if (document.getElementById(field_id).checked) {
      chk_group.push(field_id);
    } else {
      chk_group.splice(chk_group.indexOf(field_id), 1);
    }

  }

  function add_cables_array(field_id) {

    document.getElementById(field_id).value = chk_group.toString();

  }

</script>
{/literal}
<title>Kábelek</title>
<link rel="shortcut icon" type="image/png" href="images/icons8-moleskine-50.png">
</head>
<body style="padding-bottom: 70px;">
{if $error}{include file="error.tpl"}{literal}<script>javascript: $("#error").modal();</script>{/literal}{/if}
{include file="login.tpl"}
{include file="n_cable_comment.tpl"}
{include file="n_cable_new.tpl"}
