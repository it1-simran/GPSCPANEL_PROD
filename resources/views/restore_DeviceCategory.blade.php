<?php 
  use App\Helper\CommonHelper;
?>
@extends('layouts.apps')
@section('content')

<section id="main-content">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->

    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="c_breadcrumbs">
          <ul>
            <li><a href="#">Device Category</a></li>
            <li class="active"><a href="#">ReStore Device Category</a></li>
          </ul>
        </nav>
      </div>
    </div>
    <!--======== Page Title and Breadcrumbs End ========-->
    <!--======== Dynamic Datatable Content Start End ========-->
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
          <div class="row bgx-title-container">
              <div class="col-lg-6">
                <h2>Show Delete Device Categories</h2>
              </div>
              <div class="col-lg-6 text-right">
                <a href="/admin/add-device-category" class="btn btn-success"> Add Device Category </a>
              </div>
            </div>
            <div class="clearfix"></div>
          </div><!--/.c_title-->
          <div class="c_content">
            <div class="row" id="alert_msg">
              @if ($message = Session::get('success'))
              <div class="col-sm-12 alert alert-success" role="alert">
                {{ $message }}
              </div>
              @endif
              @if ($message = Session::get('error'))
              <div class="col-sm-12 alert alert-danger" role="alert">
                {{ $message }}
              </div>
              @endif
              @if ($errors->any())
              <div class="col-sm-12 alert alert-danger" role="alert">
                {{ $errors->first() }}
              </div>
              @endif
            </div>
            <div>
              <table id="example" class="table table-bordered table-striped table-condensed cf" style="border-spacing:0px; width:100%; font-size:14px;">
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Device Category Name</th>
                    <th>No of Devices</th>
                    <th style="width:12px;">Created at</th>
                    <th>Last Edit</th>
                    <th>Delete</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($device_categories) > 0)
                  <?php 
                  $i = 1;
                  ?>
                  @foreach($device_categories as $device_category)
                  <tr>
                    <td><?php echo $i; ?></td>
                    <td>{{$device_category->device_category_name}}</td>
                    <td><?php echo CommonHelper::countNoOfDevices($device_category->id);?></td>
                    <td>{{CommonHelper::getDateAsTimeZone($device_category->created_at)}}</td>
                    <td>{{CommonHelper::getDateAsTimeZone($device_category->updated_at)}}</td>
                    <td><form action="/admin/restore-device-category/{{$device_category->id}}" method="post">
                        @csrf
                        @method('PATCH')
                        <button onClick="javascript:return confirm('Are you sure you want to Restore this?');" class="btn btn-danger btn-sm" type="submit">Restore</button>
                      </form>
                    </td>
                  </tr>
                  <?php $i++; ?>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
    <!--======== Dynamic Datatable Content Start End ========-->
  </section>
</section>
@stop