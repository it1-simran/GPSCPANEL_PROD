@extends('layouts.apps')
@section('content')
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <section id="main-content">
        <section class="wrapper">
            <div class="top-page-header">
                <div class="page-breadcrumb">
                    <nav class="c_breadcrumbs">
                        <ul>
                            <li><a href="{{ route('protocols.index') }}">Protocol Management</a></li>
                            <li class="active"><a href="#">Add New Protocol</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="c_panel">
                        <div class="c_title mb-4">
                            <h2 class="m-0 font-weight-bold">Create New Protocol</h2>
                        </div>
                        <div class="section-card">
                            <div class="section-header">
                                <h4><i class="fa fa-plus-circle"></i> Protocol Identity</h4>
                            </div>
                            <div class="section-body">
                                <form class="premium-form" method="POST" action="{{ route('protocols.store') }}">
                                    @csrf
                                    <div class="form-group mb-4">
                                        <label class="premium-label">Protocol Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control premium-input" required
                                            placeholder="e.g. HTTP, MQTT, Custom Binary">
                                    </div>
                                    <div class="form-group mb-4">
                                        <label class="premium-label">Description (Optional)</label>
                                        <textarea name="description" class="form-control premium-textarea" rows="3"
                                            placeholder="Briefly describe the protocol's purpose..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end gap-3 mt-5">
                                        <a href="{{ route('protocols.index') }}"
                                            class="btn btn-glass-secondary px-4 mr-2">Cancel</a>
                                        <button type="submit" class="btn btn-premium-success px-5">
                                            <i class="fa fa-save"></i> Create Protocol
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <style>
        :root {
            --premium-primary: #6366f1;
            --premium-success: #10b981;
            --premium-dark: #0f172a;
            --premium-bg: #f1f5f9;
        }

        body {
            background-color: var(--premium-bg);
            font-family: 'Inter', sans-serif;
        }

        .section-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            overflow: hidden;
        }

        .section-header {
            background: #ffffff;
            padding: 20px 28px;
            border-bottom: 1px solid #f1f5f9;
        }

        .section-header h4 {
            color: var(--premium-dark);
            font-weight: 700;
            font-size: 1.1rem;
            font-family: 'Outfit', sans-serif;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-body {
            padding: 32px;
        }

        .premium-label {
            font-weight: 600;
            font-size: 0.75rem;
            color: #64748b;
            margin-bottom: 10px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .premium-input,
        .premium-textarea {
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            padding: 12px 16px;
            transition: all 0.2s;
            font-size: 0.95rem;
            background-color: #f8fafc;
        }

        .premium-input:focus,
        .premium-textarea:focus {
            border-color: var(--premium-primary);
            background-color: white;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .btn-premium-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            padding: 12px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .btn-glass-secondary {
            background: #f1f5f9;
            color: #64748b;
            border: 1.5px solid #e2e8f0;
            font-weight: 700;
            border-radius: 12px;
            padding: 12px 30px;
        }
    </style>
@endsection