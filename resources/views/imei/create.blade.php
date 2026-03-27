@extends('layouts.apps')

@section('content')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #2af598 0%, #009efd 100%);
        --surface-color: #ffffff;
        --border-color: #e2e8f0;
        --text-main: #2d3748;
        --text-muted: #718096;
        --accent-color: #4c51bf;
    }

    #main-content {
        background-color: #f7fafc;
        min-height: 100vh;
        padding-bottom: 50px;
    }

    .wrapper {
        padding: 30px;
    }

    .premium-card {
        background: var(--surface-color);
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        border: 1px solid var(--border-color);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        max-width: 800px;
        margin: 20px auto;
    }

    .premium-card:hover {
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    }

    .card-header-premium {
        background: var(--primary-gradient);
        padding: 30px;
        color: white;
        text-align: center;
    }

    .card-header-premium h2 {
        margin: 0;
        font-weight: 700;
        font-size: 1.5rem;
        letter-spacing: 0.5px;
        color: white !important;
    }

    .card-body-premium {
        padding: 40px;
    }

    .form-group-premium {
        margin-bottom: 25px;
    }

    .control-label-premium {
        display: block;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 8px;
        font-size: 0.95rem;
    }

    .form-control-premium {
        width: 100%;
        padding: 12px 18px;
        border-radius: 12px;
        border: 2px solid var(--border-color);
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: #fff;
    }

    .form-control-premium:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 4px rgba(76, 81, 191, 0.1);
        outline: none;
    }

    .btn-premium-save {
        background: var(--secondary-gradient);
        border: none;
        color: white;
        padding: 14px 40px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 158, 253, 0.3);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-premium-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 158, 253, 0.4);
        opacity: 0.95;
        color: white;
    }

    .btn-premium-cancel {
        background: transparent;
        border: 2px solid var(--border-color);
        color: var(--text-muted);
        padding: 12px 30px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-premium-cancel:hover {
        background: #f7fafc;
        color: var(--text-main);
        border-color: var(--text-muted);
    }

    .input-icon-wrapper {
        position: relative;
    }

    .input-icon-wrapper i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 1.1rem;
    }

    .input-icon-wrapper .form-control-premium {
        padding-left: 45px;
    }

    /* Validation Errors Style */
    .alert-premium {
        border-radius: 12px;
        background-color: #fff5f5;
        border-left: 5px solid #f56565;
        color: #c53030;
        margin-bottom: 25px;
        padding: 15px 20px;
    }

    .alert-premium ul {
        margin-bottom: 0;
        padding-left: 20px;
    }

    /* Animation on entry */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-up {
        animation: fadeInUp 0.5s ease-out forwards;
    }
</style>

<section id="main-content">
    <section class="wrapper">
        <div class="top-page-header">
            <div class="page-breadcrumb">
                <nav class="c_breadcrumbs">
                    <ul>
                        <li><a href="#">Live Tracking</a></li>
                        <li class="active font-weight-bold"><a href="#">Add Tracker</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="premium-card animate-up">
            <div class="card-header-premium">
                <h2><i class="fa fa-plus-circle mr-2"></i> Add IMEI Tracker</h2>
            </div>
            
            <div class="card-body-premium">
                @if ($errors->any())
                    <div class="alert-premium shadow-sm">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li><i class="fa fa-times-circle mr-2"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('imei-devices.store') }}">
                    @csrf
                    
                    <div class="form-group-premium">
                        <label class="control-label-premium">IMEI (15 digits) *</label>
                        <div class="input-icon-wrapper">
                            <i class="fa fa-barcode"></i>
                            <input type="text" name="imei" class="form-control-premium" required maxlength="15" placeholder="Enter 15-digit IMEI number">
                        </div>
                    </div>

                    <div class="form-group-premium">
                        <label class="control-label-premium">Status *</label>
                        <div class="input-icon-wrapper">
                            <i class="fa fa-toggle-on"></i>
                            <select name="status" class="form-control-premium" style="appearance: none; -webkit-appearance: none;" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="close">Close</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-premium">
                                <label class="control-label-premium">Schedule Start (Optional)</label>
                                <div class="input-icon-wrapper">
                                    <i class="fa fa-calendar-alt"></i>
                                    <input type="datetime-local" name="schedule_start" class="form-control-premium">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-premium">
                                <label class="control-label-premium">Schedule End (Optional)</label>
                                <div class="input-icon-wrapper">
                                    <i class="fa fa-calendar-check"></i>
                                    <input type="datetime-local" name="schedule_end" class="form-control-premium">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                        <a href="{{ route('imei-devices.index') }}" class="btn-premium-cancel">
                            <i class="fa fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn-premium-save">
                            <i class="fa fa-save"></i> Save Device
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</section>
@endsection
