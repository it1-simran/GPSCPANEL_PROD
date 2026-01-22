<!-- <div class="sidebar">
    <aside>
        <div id="sidebar" class="nav-collapse md-box-shadowed">
            <!-- sidebar menu start-->
            <div class="leftside-navigation leftside-navigation-scroll">
                <ul class="sidebar-menu" id="nav-accordion">
                    <?php if (Auth::user()->user_type == 'Admin') {
                    ?>
                        <li class=""><a href="<?php echo url('/admin'); ?>" class="hvr-bounce-to-right-sidebar-parent {{($parent_uri=='admin' && $child_uri=='') ? 'active' : '' }}"><span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span></a>
                        </li>
                        <li class='sub-menu'>
                            <a href="1" class="hvr-bounce-to-right-sidebar-parent {{($child_uri=='add-user' || $child_uri=='view-user') ? 'active' : '' }}"><span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span></a>
                            <ul class='sub'>
                                <li class="{{($child_uri=='add-user') ? 'active' : '' }}"><a href="<?php echo url('/admin/add-user'); ?>">Add Account</a>
                                </li>
                                <li class="{{($child_uri=='view-user') ? 'active' : '' }}"><a href="<?php echo url('admin/view-user'); ?>">View Account</a>
                                </li>
                                <li class="{{($child_uri=='view-user-approval-request') ? 'active' : '' }}"><a href="<?php echo url('admin/view-user-approval-request'); ?>">View Approval Request</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu '>
                            <a href="1" class="hvr-bounce-to-right-sidebar-parent {{($child_uri=='add-device' || $child_uri=='view-device') ? 'active' : '' }}"><span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span></a>
                            <ul class='sub'>
                                <li class="{{($child_uri=='add-device') ? 'active' : '' }}"><a href="<?php echo url('admin/add-device'); ?>">Add Device</a>
                                </li>
                                <li class="{{($child_uri=='add-device') ? 'active' : '' }}"><a href="<?php echo url('/admin/add-Multipledevice'); ?>">Add Multiple Device</a>
                                </li>
                                <li class="{{($child_uri=='view-device') ? 'active' : '' }}"><a href="<?php echo url('/admin/view-device-assign'); ?>">Assigned Devices</a>
                                </li>
                                <li class="{{($child_uri=='view-device') ? 'active' : '' }}"><a href="<?php echo url('/admin/view-device-unassign'); ?>">Unassigned Devices</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu '>
                            <a href="1" class="hvr-bounce-to-right-sidebar-parent {{($child_uri=='add-template' || $child_uri=='view-template') ? 'active' : '' }}"><span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span></a>
                            <ul class='sub'>
                                <li class="{{($child_uri=='add-template') ? 'active' : '' }}"><a href="<?php echo url('admin/add-template'); ?>">Add Settings </a>
                                </li>
                                <li class="{{($child_uri=='view-template') ? 'active' : '' }}"><a href="<?php echo url('admin/view-template'); ?>">View Settings</a>
                                </li>
                            </ul>
                        </li>
                    <?php
                    } else if (Auth::user()->user_type == 'Reseller') {
                    ?>
                        <li class=""><a href="<?php echo url('/reseller'); ?>" class="hvr-bounce-to-right-sidebar-parent {{($parent_uri=='reseller' && $child_uri=='') ? 'active' : '' }}"><span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span></a>
                        </li>
                        <li class='sub-menu'>
                            <a href="1" class="hvr-bounce-to-right-sidebar-parent {{($child_uri=='add-user' || $child_uri=='view-user') ? 'active' : '' }}"><span class='icon-sidebar pe-7s-user fa-2x'></span><span>Account Management</span></a>
                            <ul class='sub'>
                                <li class="{{($child_uri=='add-user') ? 'active' : '' }}"><a href="<?php echo url('/reseller/add-user'); ?>">Add Account</a>
                                </li>
                                <li class="{{($child_uri=='view-user') ? 'active' : '' }}"><a href="<?php echo url('reseller/view-user'); ?>">View Account</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu '>
                            <a href="1" class="hvr-bounce-to-right-sidebar-parent {{($child_uri=='add-device' || $child_uri=='view-device') ? 'active' : '' }}"><span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span></a>
                            <ul class='sub'>
                                <li class="{{($child_uri=='view-device') ? 'active' : '' }}"><a href="<?php echo url('/reseller/view-device-assign'); ?>">Assigned Devices</a>
                                </li>
                                <li class="{{($child_uri=='view-device') ? 'active' : '' }}"><a href="<?php echo url('/reseller/view-device-unassign'); ?>">Unassigned Devices</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu '>
                            <a href="1" class="hvr-bounce-to-right-sidebar-parent {{($child_uri=='add-template' || $child_uri=='view-template') ? 'active' : '' }}"><span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span></a>
                            <ul class='sub'>
                                <li class="{{($child_uri=='add-template') ? 'active' : '' }}"><a href="<?php echo url('reseller/add-template'); ?>">Add Settings </a>
                                </li>
                                <li class="{{($child_uri=='view-template') ? 'active' : '' }}"><a href="<?php echo url('reseller/view-template'); ?>">View Settings</a>
                                </li>
                            </ul>
                        </li>
                    <?php
                    } else {
                    ?>
                        <li class=""><a href="<?php echo url('/user'); ?>" class="hvr-bounce-to-right-sidebar-parent {{($parent_uri=='admin' && $child_uri=='') ? 'active' : '' }}"><span class='icon-sidebar icon-home fa-2x'></span><span>Dashboard</span></a>
                        </li>
                        <li class='sub-menu '>
                            <a href="1" class="hvr-bounce-to-right-sidebar-parent {{($child_uri=='add-device' || $child_uri=='view-device') ? 'active' : '' }}"><span class='icon-sidebar pe-7s-albums fa-2x'></span><span>Device Management</span></a>
                            <ul class='sub'>
                                <!--   <li class="{{($child_uri=='add-device') ? 'active' : '' }}"><a href="{{route('device.add')}}">Add Device</a>
                              </li> -->
                                <li class="{{($child_uri=='view-device') ? 'active' : '' }}"><a href="{{route('device.view')}}">View Device</a>
                                </li>
                            </ul>
                        </li>
                        <li class='sub-menu '>
                            <a href="1" class="hvr-bounce-to-right-sidebar-parent {{($child_uri=='add-template' || $child_uri=='view-template') ? 'active' : '' }}"><span class='icon-sidebar pe-7s-note fa-2x'></span><span>Settings Management</span></a>
                            <ul class='sub'>
                                <li class="{{($child_uri=='add-template') ? 'active' : '' }}"><a href="{{route('template.add')}}">Add Settings </a>
                                </li>
                                <li class="{{($child_uri=='view-template') ? 'active' : '' }}"><a href="{{route('template.view')}}">View Settings</a>
                                </li>
                            </ul>
                        </li>
                    <?php
                    }
                    ?>
                </ul>
            </div>
            <!-- sidebar menu end-->
        </div>
    </aside>
</div> -->