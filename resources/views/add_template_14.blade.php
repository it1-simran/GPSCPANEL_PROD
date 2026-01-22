 @extends('layouts.apps')

@section('content')
 <!--main content start-->
        <section id="main-content">

                
            <section class="wrapper">
              

                <!--======== Page Title and Breadcrumbs Start ========-->
                <div class="top-page-header">
                    
                   
                    <div class="page-breadcrumb">
                        <nav class="c_breadcrumbs">
                            <ul>
                                <li><a href="#">Settings</a></li>
                                <li class="active"><a href="#">Add Settings</a></li>
                                
                            </ul>
                        </nav>
                    </div>
                   

                  
                </div>
                <!--======== Page Title and Breadcrumbs End ========-->

                <!--======== Form Validation Content Start End ========-->
                <div class="row">

                    <div class="col-md-12">
                        
                        <!--=========== START TAGS INPUT ===========-->
                        

                        <div class="c_panel">

                            <div class="c_title">
                                <h2>Add Settings</h2>
                                <div class="clearfix"></div>
                            </div><!--/.c_title-->

                            <div class="c_content">

                             <div class="row" id="alert_msg">
                              <!-- <div class="col-sm-7">
                             </div> -->
                           @if ($message = Session::get('success'))
                          
                          <div class="col-sm-12 alert alert-success" role="alert" >
                                 {{ $message }}
                              </div>
                          
                             @endif
                       
                                @if ($message = Session::get('error'))
                                      
                                <div class="col-sm-12 alert alert-danger" role="alert" >

                                 {{ $message }}
                            
                            </div>
                                @endif
                                @if ($errors->any())
                              
                                <div class="col-sm-12 alert alert-danger" role="alert">
                                {{ $errors->first() }}
                              </div>
                             @endif
                              </div>
                              <!-- arjun -->


                                <form class="validator form-horizontal " id="commentForm" method="post" action="<?php echo url((Auth::user()->user_type=='Admin'?'admin':'user').'/store-template'); ?>" >
                                    @csrf 
                                   
                                    <div class="form-group ">
                                        <label for="curl" class="control-label col-lg-3">Template Name (required)</label>
                                        <div class="col-lg-6">
                                            <input class="form-control " id="template_name" type="text" name="template_name" required  onkeypress="return blockSpecialChar(event)"/>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label for="curl" class="control-label col-lg-3">IP (required)</label>
                                        <div class="col-lg-6">
                                            <input class="form-control" placeholder="Enter IP address or URL" id="ip" type="text" name="ip"  required  />
                                        </div>
                                    </div>

                                     <div class="form-group ">
                                        <label for="curl" class="control-label col-lg-3">Port (required)</label>
                                        <div class="col-lg-6">
                                            <input class="form-control" placeholder="Enter Port Number" id="port" type="Number" name="port" max="5"  required onkeypress="return blockSpecialCharport(event)"/>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label for="curl" class="control-label col-lg-3">Logs Interval (required)</label>
                                        <div class="col-lg-6">
                                            <input class="form-control" placeholder="Data rate at ignition ON (in seconds)" id="logs_interval" type="Number"   name="logs_interval" value="20" required onkeypress="return blockSpecialCharLogs(event)"/>
                                        </div>
                                    </div>

                                     <div class="form-group ">
                                        <label for="curl" class="control-label col-lg-3">Sleep Interval (required)</label>
                                        <div class="col-lg-6">
                                            <input class="form-control" placeholder="Data rate at ignition OFF (in seconds)" id="sleep_interval" type="Number" name="sleep_interval" value="300" required onkeypress="return blockSpecialCharSleep(event)"/>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label for="curl" class="control-label col-lg-3">Transmission Interval (required)</label>
                                        <div class="col-lg-6">
                                            <input class="form-control" placeholder="Keep 0 seconds to get live location without delay" id="trans_interval" type="text" name="trans_interval" value="0" required onkeypress="return blockSpecialCharTransmission(event)"/>
                                        </div>
                                    </div>

                                    <div class="form-group ">
                                        <label for="curl" class="control-label col-lg-3">Password (required)</label>
                                        <div class="col-lg-6">
                                            <input class="form-control" placeholder="Enter 4 digit device password" id="password" type="text" name="password" value="1234" required  onkeypress="return isNumberKey(this, event);"/>
                                        </div>
                                    </div>

                                     <div class="form-group ">
                                        <label for="curl" class="control-label col-lg-3">Active Status</label>
                                        <div class="col-lg-6">
                                            <select class="form-control" name="active_status">
                                                <option value="1" selected>Yes</option>
                                                <option value="0">No</option>
                                            </select>
                                        </div>
                                    </div>

                                     <div class="form-group ">
                                        <label for="curl" class="control-label col-lg-3">Fota (required)</label>
                                        <div class="col-lg-6">
                                            <select class="form-control" name="fota">
                                                <option value="1" >Yes</option>
                                                <option value="0" selected>No</option>
                                            </select>
                                        </div>
                                    </div>



                                    <div class="form-group">
                                        <div class="col-lg-offset-3 col-lg-6">
                                            <button class="btn btn-primary btn-flat" type="submit">Save</button>
                                            <button class="btn btn-default btn-flat" type="button">Cancel</button>
                                        </div>
                                    </div>
                                </form>

                                <hr>

                               
                                
                                
                            </div><!--/.c_content-->

                        </div><!--/.c_panels-->

                    </div>

                </div>
                <!--======== Form Validation Content Start End ========-->


            </section>

        </section>
        <!--======== Main Content End ========-->
@stop
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<script type="text/javascript">
$( document ).ready(function() {

  $("#logs_interval").on("change", function() {
   var x=this.value;
    if (x==null || x >9999|| x <5)
      {
      alert("Value must be between 5 to 9999");
      $("#logs_interval").val('');
      return false;
      }
});

$("#sleep_interval").on("change", function() {
    // alert(this.value);
     var x=this.value;
    if (x==null || x >9999|| x <120)
      {
      alert("Value must be between 120 to 9999");
      $("#sleep_interval").val('');
      return false;
      }
});
$("#trans_interval").on("change", function() {
    // alert(this.value);
     var x=this.value;
    if (x==null || x >9999|| x <0)
      {
      alert("Value must be between 0 to 9999");
      $("#trans_interval").val('');
      return false;
      }
});

$("#password").on("change", function() {
     var myLength = $("#password").val().length;
    // alert(myLength);

    if (myLength >4 || myLength <4)
      {
      alert("Password only 4 digit allowed");
      $("#password").val('');
      return false;
      }
});
  
});
</script>