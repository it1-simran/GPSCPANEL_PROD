<!DOCTYPE html>
<html lang="en" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>M2M Messages</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .chat-bubble-out { background-color: #1a73e8; color: white; border-radius: 18px 18px 4px 18px; }
        .chat-bubble-in { background-color: #f1f3f4; color: #3c4043; border-radius: 18px 18px 18px 4px; }
        .sidebar-item:hover { background-color: #f8f9fa; }
        .sidebar-item.active { background-color: #e8f0fe; color: #1a73e8; }
        .modal-bg { background-color: rgba(0,0,0,0.5); }
        .dropdown-menu { display: none; position: absolute; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border: 1px solid #f1f3f4; z-index: 100; min-width: 160px; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { padding: 10px 16px; font-size: 14px; color: #3c4043; cursor: pointer; transition: background 0.2s; }
        .dropdown-item:hover { background-color: #f8f9fa; }
        @media (max-width: 767px) {
            body.chat-open #sidebar { display: none !important; }
            body.chat-open #mainContent { display: flex !important; }
            body:not(.chat-open) #mainContent { display: none !important; }
            body:not(.chat-open) #sidebar { display: flex !important; }
        }
 
    </style>
</head>
<body class="h-full overflow-hidden flex flex-col">
    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-[9999] flex flex-col gap-2"></div>

    <div class="flex h-full w-full">
        <!-- Sidebar -->
        <div id="sidebar" class="w-full md:w-80 lg:w-96 border-r border-gray-200 flex flex-col bg-white shrink-0">
            <div class="p-4 flex flex-col gap-4 border-b border-gray-100">
                <div class="flex items-center justify-between" id="sidebarTitle">
                    <h1 class="text-xl font-medium text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-blue-600">forum</span>
                        Messages
                    </h1>
                    <div class="flex gap-2 relative">
                        <button onclick="toggleSearch()" class="p-2 hover:bg-gray-100 rounded-full text-gray-600">
                            <span class="material-symbols-outlined">search</span>
                        </button>
                        <div class="relative">
                            <button onclick="toggleSidebarMenu(event)" class="p-2 hover:bg-gray-100 rounded-full text-gray-600">
                                <span class="material-symbols-outlined">more_vert</span>
                            </button>
                            <div id="sidebarMenu" class="dropdown-menu right-0 mt-2">
                                <div onclick="location.reload()" class="dropdown-item flex items-center gap-3">
                                    <span class="material-symbols-outlined text-sm">refresh</span>
                                    Refresh fleet
                                </div>
                                <div onclick="showSettingsModal()" class="dropdown-item flex items-center gap-3 border-t border-gray-100">
                                    <span class="material-symbols-outlined text-sm">settings</span>
                                    Settings
                                </div>
                                
                                <form method="POST" action="{{ route('logout') }}" id="logoutForm" class="hidden">
                                    @csrf
                                </form>
                                <div onclick="document.getElementById('logoutForm').submit()" class="dropdown-item flex items-center gap-3 border-t border-gray-100 text-rose-500 font-medium">
                                    <span class="material-symbols-outlined text-sm">logout</span>
                                    Sign out
                                </div>

                                <div class="dropdown-item flex items-center gap-3 opacity-40 border-t border-gray-100">
                                    <span class="material-symbols-outlined text-sm">archive</span>
                                    Archived
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Search Bar (Hidden by default) -->
                <div id="searchBar" class="hidden flex items-center gap-2 bg-gray-100 rounded-full px-4 py-2">
                    <span class="material-symbols-outlined text-gray-400">search</span>
                    <input type="text" id="searchInput" oninput="filterDevices()" placeholder="Search messages..." 
                           class="bg-transparent flex-1 focus:outline-none text-sm text-gray-700">
                    <button onclick="toggleSearch()" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            </div>

            <!-- Device List -->
            <div class="flex-1 overflow-y-auto scrollbar-hide py-2" id="deviceList">
                @foreach($devices as $device)
                <div onclick="selectDevice({{ $device->id }})" 
                     id="device-{{ $device->id }}"
                     data-name="{{ $device->name }}"
                     data-number="{{ $device->phone_number }}"
                     data-imei="{{ $device->imei }}"
                     data-is-active="{{ $device->is_active ? '1' : '0' }}"
                     data-last-seen="{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}"
                     class="sidebar-item group px-4 py-3 cursor-pointer flex items-center gap-4 transition-colors">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold shrink-0">
                        {{ strtoupper(substr($device->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline">
                            <h3 class="font-medium text-sm truncate">{{ $device->name }}</h3>
                            <span class="text-[10px] text-gray-400 shrink-0 time-display group-hover:hidden" data-utc="{{ $device->logs->first() ? $device->logs->first()->created_at->toISOString() : $device->created_at->toISOString() }}">
                                {{ $device->logs->first() ? $device->logs->first()->created_at->format('H:i') : $device->created_at->format('H:i') }}
                            </span>
                            <button onclick="deleteDeviceSidebar({{ $device->id }}, event, '{{ addslashes($device->name) }}')" class="hidden group-hover:block text-rose-500 hover:bg-rose-100 p-1 rounded-full transition-all shrink-0" title="Delete Device">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 truncate mt-1">
                            {{ $device->logs->first()?->content ?? 'No messages yet' }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- FAB: Start Chat -->
            <!-- <div class="p-4 absolute bottom-4 left-64 md:left-80 z-10">
                <button onclick="showAddDeviceModal()" class="w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl shadow-lg flex items-center justify-center transition-transform hover:scale-105">
                    <span class="material-symbols-outlined text-3xl">add</span>
                </button>
            </div> -->
            <div class="absolute bottom-6 right-6 z-50">
                <button onclick="showAddDeviceModal()"
                    class="w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-md flex items-center justify-center transition hover:scale-105">
                    
                    <span class="material-symbols-outlined text-xl">add</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col bg-white min-w-0" id="mainContent">
            <!-- Empty State -->
            <div id="emptyState" class="flex-1 flex flex-col items-center justify-center text-gray-400 p-8 text-center">
                <span class="material-symbols-outlined text-8xl mb-4 opacity-20">chat_bubble</span>
                <h2 class="text-xl font-medium text-gray-600">Select a device to start messaging</h2>
                <p class="mt-2 max-w-sm">Manage your M2M fleet directly through SMS commands and receive real-time updates.</p>
                <button onclick="showAddDeviceModal()" class="mt-6 px-6 py-2 bg-blue-600 text-white rounded-full font-medium hover:bg-blue-700 transition-colors">
                    Start new chat
                </button>
            </div>

            <!-- Chat View (Hidden by default) -->
            <div id="chatView" class="hidden flex-1 flex flex-col h-full overflow-hidden">
                <!-- Chat Header -->
                <div class="px-6 py-3 border-b border-gray-200 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-2 md:gap-4">
                        <button onclick="showSidebar()" class="md:hidden p-2 -ml-2 hover:bg-gray-100 rounded-full text-gray-600 transition-colors">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </button>
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold shrink-0" id="chatHeaderInitial">
                            M
                        </div>
                        <div>
                            <h2 class="font-medium text-gray-900" id="chatHeaderName">Device Name</h2>
                            <p class="text-[10px] flex items-center gap-1 text-gray-500">
                                <span id="statusPing" class="w-2 h-2 rounded-full bg-gray-300 transition-all"></span>
                                <span id="chatHeaderStatus">Connecting...</span> | 
                                <span id="chatHeaderNumber">...</span>
                            </p>
                        </div>
                    </div>
                    <div id="chatHeaderActions" class="flex gap-2 text-gray-600">
                        <button onclick="toggleChatSearch()" class="p-2 hover:bg-gray-100 rounded-full"><span class="material-symbols-outlined">search</span></button>
                        <button onclick="removeDevice()" class="p-2 hover:bg-red-50 rounded-full text-red-500 transition-colors" title="Remove Device">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                        <button onclick="showDeviceInfo()" class="p-2 hover:bg-gray-100 rounded-full"><span class="material-symbols-outlined">info</span></button>
                    </div>
                    <!-- Chat Search Bar (Hidden) -->
                    <div id="chatSearchBar" class="hidden flex items-center gap-2 bg-gray-100 rounded-full px-4 py-2 w-64 lg:w-80">
                        <span class="material-symbols-outlined text-gray-400">search</span>
                        <input type="text" id="chatSearchInput" oninput="filterMessages()" placeholder="Search in conversation..." 
                               class="bg-transparent flex-1 focus:outline-none text-sm text-gray-700">
                        <button onclick="toggleChatSearch()" class="text-gray-400 hover:text-gray-600">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4 flex flex-col-reverse scrollbar-hide" id="messageContainer">
                    <!-- Messages will be injected here (latest first due to flex-col-reverse) -->
                </div>

                <!-- Input Area -->
                <div class="p-4 border-t border-gray-100">
                    <div class="max-w-4xl mx-auto flex items-end gap-2 bg-gray-50 rounded-3xl px-4 py-2 border border-gray-200">
                        <div class="relative group">
                            <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition-colors">
                                <span class="material-symbols-outlined">add_circle</span>
                            </button>
                            <!-- Templates Tooltip -->
                            <div class="absolute bottom-12 left-0 hidden group-hover:block w-64 bg-white shadow-xl rounded-2xl border border-gray-100 p-2 z-20">
                                <p class="text-[10px] uppercase font-bold text-gray-400 px-3 py-2">Quick Commands</p>
                                <div id="templatesContainer">
                                    @foreach($templates as $template)
                                    <div class="relative group/item flex items-center justify-between hover:bg-blue-50 rounded-xl transition-colors" id="template-{{ $template->id }}">
                                        <button onclick="setCommand('{{ $template->payload }}')" class="w-full text-left px-3 py-2">
                                            <span class="block text-sm font-medium">{{ $template->label }}</span>
                                            <span class="block text-[10px] text-gray-400 font-mono">{{ $template->payload }}</span>
                                        </button>
                                        <button onclick="deleteTemplate({{ $template->id }}, event)" class="absolute right-2 opacity-0 group-hover/item:opacity-100 p-1 text-rose-500 hover:bg-rose-100 rounded-full transition-all">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input type="text" id="commandInput" placeholder="Send a message" 
                               class="flex-1 bg-transparent py-2 px-2 focus:outline-none text-gray-700"
                               onkeypress="handleKeyPress(event)">
                        <button onclick="showSaveTemplateModal()" class="p-2 text-gray-400 hover:text-yellow-500 hover:bg-yellow-50 rounded-full transition-colors" title="Save as Quick Command">
                            <span class="material-symbols-outlined">star</span>
                        </button>
                        <button onclick="executeCommand()" id="sendBtn" class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition-colors disabled:opacity-30">
                            <span class="material-symbols-outlined">send</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Device Modal -->
    <div id="addDeviceModal" class="fixed inset-0 modal-bg z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl transition-all">
            <div class="p-6">
                <h3 class="text-xl font-medium mb-4">New message</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">To (Phone Number)</label>
                        <input type="text" id="newDevicePhone" placeholder="+1234567890" 
                               class="w-full bg-gray-100 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Device Name (Optional)</label>
                        <input type="text" id="newDeviceName" placeholder="Alpha Tracker" 
                               class="w-full bg-gray-100 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 p-4 flex justify-end gap-3">
                <button onclick="hideAddDeviceModal()" class="px-6 py-2 text-gray-600 hover:bg-gray-200 rounded-full font-medium transition-colors">Cancel</button>
                <button onclick="addDevice()" class="px-6 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-full font-medium shadow-md transition-all">Start Chat</button>
            </div>
        </div>
    </div>
    <!-- Info Modal -->
    <div id="infoModal" class="fixed inset-0 modal-bg z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl transition-all border border-gray-100">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <h3 class="text-xl font-medium">Device Info</h3>
                    <button onclick="hideInfoModal()" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-2xl font-bold" id="infoInitial">
                            M
                        </div>
                        <div>
                            <p class="font-bold text-lg" id="infoName">Device Name</p>
                            <p class="text-xs text-blue-500 font-medium" id="infoStatus">Active</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4 pt-4 border-t border-gray-100">
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Phone Number</p>
                            <p class="text-gray-700" id="infoNumber">+1234567890</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">IMEI</p>
                            <p class="text-gray-700 font-mono text-sm" id="infoImei">359612000000001</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Last Activity</p>
                            <p class="text-gray-700" id="infoLastSeen">12 minutes ago</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 p-4 space-y-3">
                <div class="p-3 bg-blue-50 rounded-2xl border border-blue-100">
                    <p class="text-[10px] uppercase font-bold text-blue-600 mb-2">Simulator: Mock Response</p>
                    <div class="flex gap-2">
                        <input type="text" id="simResponseText" placeholder="e.g. OK: BATTERY 90%" 
                               class="flex-1 text-xs bg-white border border-blue-200 rounded-lg px-3 py-2 focus:outline-none">
                        <button onclick="simulateResponse()" class="bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors">
                            Trigger
                        </button>
                    </div>
                </div>
                <button onclick="hideInfoModal()" class="w-full py-3 bg-gray-200 hover:bg-gray-300 rounded-2xl font-medium text-gray-700 transition-all">Close</button>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div id="settingsModal" class="fixed inset-0 modal-bg z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl transition-all border border-gray-100">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <h2 class="text-xl font-medium">Settings</h2>
                    <button onclick="hideSettingsModal()" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined">close</span></button>
                </div>
                <div class="space-y-6">
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Webhook Configuration</h4>
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200">
                            <p class="text-[10px] text-gray-500 mb-1">Incoming SMS Endpoint</p>
                            <p class="text-sm font-mono text-blue-600 truncate">/api/webhooks/sms</p>
                            <div class="mt-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-xs font-medium text-emerald-600">Active & Hub-Ready</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">System Status</h4>
                        <div class="flex items-center justify-between p-1">
                            <span class="text-sm text-gray-600">Total Devices</span>
                            <span class="text-sm font-medium text-gray-900">{{ $devices->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between p-1">
                            <span class="text-sm text-gray-600">Active Gateways</span>
                            <span class="text-sm font-medium text-gray-900">1</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 p-4">
                <button onclick="hideSettingsModal()" class="w-full py-3 bg-blue-600 hover:bg-blue-700 rounded-2xl font-medium text-white shadow-md transition-all">Done</button>
            </div>
        </div>
    </div>

    <!-- Save Template Modal -->
    <div id="saveTemplateModal" class="fixed inset-0 modal-bg z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl transition-all border border-gray-100">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <h3 class="text-xl font-medium">Save Quick Command</h3>
                    <button onclick="hideSaveTemplateModal()" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-2">Command Name</label>
                        <input type="text" id="templateLabel" placeholder="e.g., Battery Check" 
                               class="w-full bg-gray-100 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-2">Description (Optional)</label>
                        <textarea id="templateDescription" placeholder="What does this command do?" 
                                  class="w-full bg-gray-100 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all resize-none" rows="2"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-2">Command Payload</label>
                        <div class="bg-gray-100 rounded-xl px-4 py-3 border border-gray-200 text-sm text-gray-700 font-mono break-all" id="payloadPreview">
                            (empty)
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 p-4 flex justify-end gap-3">
                <button onclick="hideSaveTemplateModal()" class="px-6 py-2 text-gray-600 hover:bg-gray-200 rounded-full font-medium transition-colors">Cancel</button>
                <button onclick="saveTemplate()" class="px-6 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-full font-medium shadow-md transition-all">Save Command</button>
            </div>
        </div>
    </div>

    <script>
        let currentDeviceId = null;
        let pollInterval = null;

        function formatLocalTime(utcString) {
            if (!utcString) return '';
            let d = new Date(utcString);
            if (!utcString.includes('Z') && !utcString.includes('+')) {
                d = new Date(utcString + 'Z');
            }
            if (isNaN(d.getTime())) return '';
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
        }

        function updateTimeDisplays() {
            document.querySelectorAll('.time-display').forEach(el => {
                const utcStr = el.getAttribute('data-utc');
                if (utcStr) {
                    el.innerText = formatLocalTime(utcStr);
                }
            });
        }
        
        document.addEventListener('DOMContentLoaded', updateTimeDisplays);

        async function deleteDeviceSidebar(id, event, name) {
            event.stopPropagation();
            if (!confirm(`Are you sure you want to remove ${name}? This will delete all logs as well.`)) return;

            try {
                const response = await fetch(`{{ strtolower(Auth::user()->user_type) == 'support' ? '/support/sms-portal' : '/admin/sms-portal' }}/devices/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                if (data.status === 'success') {
                    showToast('Device removed', 'success');
                    const el = document.getElementById(`device-${id}`);
                    if (el) el.remove();
                    if (currentDeviceId === id) {
                        currentDeviceId = null;
                        document.getElementById('chatView').classList.add('hidden');
                        document.getElementById('emptyState').classList.remove('hidden');
                    }
                } else {
                    showToast('Failed to remove device', 'error');
                }
            } catch (e) {
                console.error(e);
                showToast('Connection error', 'error');
            }
        }

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            const isSuccess = type === 'success';
            
            toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-lg text-sm font-medium text-white transform transition-all duration-300 translate-x-full opacity-0 ${isSuccess ? 'bg-emerald-500' : 'bg-rose-500'}`;
            
            toast.innerHTML = `
                <span class="material-symbols-outlined text-[20px]">${isSuccess ? 'check_circle' : 'error'}</span>
                <span>${message}</span>
            `;
            
            container.appendChild(toast);
            
            // Trigger animation
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 10);
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 3000);
        }

        function showAddDeviceModal() {
            document.getElementById('addDeviceModal').classList.remove('hidden');
        }

        function hideAddDeviceModal() {
            document.getElementById('addDeviceModal').classList.add('hidden');
        }

        async function addDevice() {
            const phone = document.getElementById('newDevicePhone').value;
            const name = document.getElementById('newDeviceName').value;

            if (!phone) return showToast('Phone number is required', 'error');

            try {
                const response = await fetch('{{ request()->is("admin/*") ? route("admin.sms.devices.store") : route("support.sms.devices.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ phone_number: phone, name: name })
                });
                
                const data = await response.json();
                if (data.status === 'success') {
                    location.reload(); // Simplest way to update sidebar
                } else {
                    showToast(data.message || 'Error adding device', 'error');
                }
            } catch (e) {
                console.error(e);
                showToast('Connection error', 'error');
            }
        }

        function selectDevice(id) {
            currentDeviceId = id;
            document.body.classList.add('chat-open');
            
            // UI Updates
            const item = document.getElementById(`device-${id}`);
            document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
            item.classList.add('active');
            
            document.getElementById('emptyState').classList.add('hidden');
            document.getElementById('chatView').classList.remove('hidden');

            const deviceName = document.querySelector(`#device-${id} h3`).innerText;
            const deviceInitial = deviceName.charAt(0).toUpperCase();
            
            document.getElementById('chatHeaderName').innerText = deviceName;
            document.getElementById('chatHeaderInitial').innerText = deviceInitial;
            document.getElementById('chatHeaderNumber').innerText = item.getAttribute('data-number');
            
            const isActive = item.getAttribute('data-is-active') === '1';
            const statusPing = document.getElementById('statusPing');
            const statusText = document.getElementById('chatHeaderStatus');
            
            if (isActive) {
                statusPing.className = 'w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_5px_#10b981] transition-all';
                statusText.innerText = 'Online';
            } else {
                statusPing.className = 'w-2 h-2 rounded-full bg-rose-500 transition-all';
                statusText.innerText = 'Offline';
            }
            
            fetchLogs(id);
            
            // Start Polling
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(() => fetchLogs(id), 5000);
        }

        async function fetchLogs(id) {
            try {
                const response = await fetch(`{{ strtolower(Auth::user()->user_type) == 'support' ? '/support/sms-portal' : '/admin/sms-portal' }}/devices/${id}/logs`);
                const data = await response.json();
                if (data.status === 'success') {
                    renderMessages(data.logs);
                    
                    // Update header status (mock logic based on last log)
                    const statusPing = document.getElementById('statusPing');
                    const statusText = document.getElementById('chatHeaderStatus');
                    statusPing.className = 'w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_5px_#10b981]';
                    statusText.innerText = 'Online';

                    // Automatically update timing and preview in the sidebar
                    if (data.logs && data.logs.length > 0) {
                        const latestLog = data.logs[0];
                        const sidebarItem = document.getElementById(`device-${id}`);
                        if (sidebarItem) {
                            const timeSpan = sidebarItem.querySelector('.time-display');
                            const previewP = sidebarItem.querySelector('p.text-xs.text-gray-500.truncate.mt-1');
                            if (timeSpan) {
                                timeSpan.setAttribute('data-utc', latestLog.created_at);
                                timeSpan.innerText = formatLocalTime(latestLog.created_at);
                            }
                            if (previewP) {
                                previewP.innerText = latestLog.content;
                            }
                        }
                    }
                }
            } catch (e) {
                console.error('Polling error:', e);
            }
        }

        function renderMessages(logs) {
            const container = document.getElementById('messageContainer');
            container.innerHTML = '';
            
            logs.forEach((log, index) => {
                const isOut = log.direction === 'outbound';
                const div = document.createElement('div');
                div.className = `message-wrap flex ${isOut ? 'justify-end' : 'justify-start'} w-full`;
                div.setAttribute('data-content', log.content.toLowerCase());
                
                let d = new Date(log.created_at);
                if (!log.created_at.includes('Z') && !log.created_at.includes('+')) {
                    d = new Date(log.created_at + 'Z');
                }
                const time = isNaN(d.getTime()) ? '' : d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
                
                div.innerHTML = `
                    <div class="max-w-[80%]">
                        <div class="px-4 py-2 ${isOut ? 'chat-bubble-out shadow-sm' : 'chat-bubble-in shadow-sm'} text-sm">
                            ${log.content}
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1 px-1 ${isOut ? 'text-right' : 'text-left'}">${time}</p>
                    </div>
                `;
                container.appendChild(div);

                if (!isNaN(d.getTime())) {
                    let nextLog = logs[index + 1];
                    let showDivider = false;
                    
                    if (!nextLog) {
                        showDivider = true;
                    } else {
                        let nextD = new Date(nextLog.created_at);
                        if (!nextLog.created_at.includes('Z') && !nextLog.created_at.includes('+')) {
                            nextD = new Date(nextLog.created_at + 'Z');
                        }
                        if (d.toDateString() !== nextD.toDateString()) {
                            showDivider = true;
                        }
                    }

                    if (showDivider) {
                        const today = new Date();
                        const yesterday = new Date(today);
                        yesterday.setDate(yesterday.getDate() - 1);
                        
                        let dateLabel = '';
                        if (d.toDateString() === today.toDateString()) {
                            dateLabel = 'Today';
                        } else if (d.toDateString() === yesterday.toDateString()) {
                            dateLabel = 'Yesterday';
                        } else {
                            dateLabel = d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
                        }

                        const dividerDiv = document.createElement('div');
                        dividerDiv.className = 'w-full flex justify-center my-4';
                        dividerDiv.innerHTML = `<span class="bg-gray-100 text-gray-500 text-[10px] uppercase font-bold px-3 py-1 rounded-full shadow-sm">${dateLabel}</span>`;
                        container.appendChild(dividerDiv);
                    }
                }
            });
        }

        function showSidebar() {
            document.body.classList.remove('chat-open');
            currentDeviceId = null;
            if (pollInterval) clearInterval(pollInterval);
            document.querySelectorAll('.sidebar-item').forEach(el => el.classList.remove('active'));
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('chatView').classList.add('hidden');
        }

        function setCommand(payload) {
            document.getElementById('commandInput').value = payload;
        }

        function handleKeyPress(e) {
            if (e.key === 'Enter') executeCommand();
        }

        async function executeCommand() {
            if (!currentDeviceId) return;
            const input = document.getElementById('commandInput');
            const cmd = input.value;
            if (!cmd) return;

            // Optimistic Update: Append bubble immediately
            const container = document.getElementById('messageContainer');
            const optimisticDiv = document.createElement('div');
            optimisticDiv.className = 'flex justify-end w-full opacity-50';
            optimisticDiv.innerHTML = `
                <div class="max-w-[80%]">
                    <div class="px-4 py-2 chat-bubble-out shadow-sm text-sm">
                        ${cmd}
                    </div>
                    <p class="text-[10px] text-gray-400 mt-1 px-1 text-right">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false })} (Sending...)</p>
                </div>
            `;
            container.prepend(optimisticDiv); 
            input.value = '';

            try {
                const response = await fetch(`{{ strtolower(Auth::user()->user_type) == 'support' ? '/support/sms-portal' : '/admin/sms-portal' }}/devices/${currentDeviceId}/command`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ command: cmd })
                });
                
                const data = await response.json();
                if (data.status === 'success') {
                    fetchLogs(currentDeviceId); // Refresh actual logs
                } else {
                    showToast('Failed to send command', 'error');
                }
            } catch (e) {
                showToast('Error sending command', 'error');
            }
        }

        async function removeDevice() {
            if (!currentDeviceId) return;
            
            const deviceName = document.getElementById('chatHeaderName').innerText;
            if (!confirm(`Are you sure you want to remove ${deviceName}? This will delete all logs as well.`)) return;

            try {
                const response = await fetch(`{{ strtolower(Auth::user()->user_type) == 'support' ? '/support/sms-portal' : '/admin/sms-portal' }}/devices/${currentDeviceId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                if (data.status === 'success') {
                    location.reload(); // Refresh to update sidebar
                } else {
                    showToast('Failed to remove device', 'error');
                }
            } catch (e) {
                console.error(e);
                showToast('Connection error', 'error');
            }
        }

        function toggleSearch() {
            const title = document.getElementById('sidebarTitle');
            const bar = document.getElementById('searchBar');
            const input = document.getElementById('searchInput');
            
            if (bar.classList.contains('hidden')) {
                title.classList.add('hidden');
                bar.classList.remove('hidden');
                input.focus();
            } else {
                title.classList.remove('hidden');
                bar.classList.add('hidden');
                input.value = '';
                filterDevices();
            }
        }

        function filterDevices() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.sidebar-item').forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                const number = item.getAttribute('data-number').toLowerCase();
                if (name.includes(query) || number.includes(query)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }

        function showDeviceInfo() {
            if (!currentDeviceId) return;
            const item = document.getElementById(`device-${currentDeviceId}`);
            const isActive = item.getAttribute('data-is-active') === '1';
            
            document.getElementById('infoName').innerText = item.getAttribute('data-name');
            document.getElementById('infoNumber').innerText = item.getAttribute('data-number');
            document.getElementById('infoImei').innerText = item.getAttribute('data-imei');
            document.getElementById('infoLastSeen').innerText = item.getAttribute('data-last-seen');
            document.getElementById('infoInitial').innerText = item.getAttribute('data-name').charAt(0).toUpperCase();
            
            const statusEl = document.getElementById('infoStatus');
            statusEl.innerText = isActive ? 'Active' : 'Disconnected';
            statusEl.className = `text-xs font-medium ${isActive ? 'text-emerald-500' : 'text-rose-500'}`;
            
            document.getElementById('infoModal').classList.remove('hidden');
        }

        function hideInfoModal() {
            document.getElementById('infoModal').classList.add('hidden');
        }

        async function simulateResponse() {
            if (!currentDeviceId) return;
            const text = document.getElementById('simResponseText').value || "MOCK RESPONSE: Success";
            
            try {
                const response = await fetch(`{{ strtolower(Auth::user()->user_type) == 'support' ? '/support/sms-portal' : '/admin/sms-portal' }}/devices/${currentDeviceId}/simulate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ text: text })
                });
                
                const data = await response.json();
                if (data.status === 'success') {
                    document.getElementById('simResponseText').value = '';
                    hideInfoModal();
                    fetchLogs(currentDeviceId); // Refresh chat
                }
            } catch (e) {
                console.error(e);
            }
        }

        function toggleSidebarMenu(event) {
            event.stopPropagation();
            document.getElementById('sidebarMenu').classList.toggle('show');
        }

        function showSettingsModal() {
            document.getElementById('sidebarMenu').classList.remove('show');
            document.getElementById('settingsModal').classList.remove('hidden');
        }

        function hideSettingsModal() {
            document.getElementById('settingsModal').classList.add('hidden');
        }

        function showSaveTemplateModal() {
            const command = document.getElementById('commandInput').value.trim();
            if (!command) {
                showToast('Please enter a command first', 'error');
                return;
            }
            document.getElementById('payloadPreview').textContent = command;
            document.getElementById('templateLabel').value = '';
            document.getElementById('templateDescription').value = '';
            document.getElementById('saveTemplateModal').classList.remove('hidden');
        }

        function hideSaveTemplateModal() {
            document.getElementById('saveTemplateModal').classList.add('hidden');
        }

        async function saveTemplate() {
            const label = document.getElementById('templateLabel').value.trim();
            const description = document.getElementById('templateDescription').value.trim();
            const payload = document.getElementById('commandInput').value.trim();

            if (!label) {
                showToast('Please enter a command name', 'error');
                return;
            }

            try {
                const response = await fetch('{{ request()->is("admin/*") ? route("admin.sms.template.store") : route("support.sms.template.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ label, description, payload })
                });

                const data = await response.json();
                if (data.status === 'success') {
                    showToast('Command saved successfully!', 'success');
                    hideSaveTemplateModal();
                    
                    const templatesContainer = document.getElementById('templatesContainer');
                    if (templatesContainer) {
                        const template = data.template;
                        const div = document.createElement('div');
                        div.className = 'relative group/item flex items-center justify-between hover:bg-blue-50 rounded-xl transition-colors';
                        div.id = `template-${template.id}`;
                        div.innerHTML = `
                            <button onclick="setCommand('${template.payload}')" class="w-full text-left px-3 py-2">
                                <span class="block text-sm font-medium">${template.label}</span>
                                <span class="block text-[10px] text-gray-400 font-mono">${template.payload}</span>
                            </button>
                            <button onclick="deleteTemplate(${template.id}, event)" class="absolute right-2 opacity-0 group-hover/item:opacity-100 p-1 text-rose-500 hover:bg-rose-100 rounded-full transition-all">
                                <span class="material-symbols-outlined text-sm">delete</span>
                            </button>
                        `;
                        templatesContainer.appendChild(div);
                    }
                } else {
                    showToast(data.message || 'Error saving command', 'error');
                }
            } catch (e) {
                console.error(e);
                showToast('Connection error', 'error');
            }
        }

        async function deleteTemplate(id, event) {
            event.stopPropagation();
            if (!confirm('Are you sure you want to delete this command?')) return;

            try {
                const url = '{{ request()->is("admin/*") ? "/admin/sms-portal/template/" : "/support/sms-portal/template/" }}' + id;
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const data = await response.json();
                if (data.status === 'success') {
                    showToast('Command deleted successfully!', 'success');
                    const element = document.getElementById(`template-${id}`);
                    if (element) element.remove();
                } else {
                    showToast(data.message || 'Error deleting command', 'error');
                }
            } catch (e) {
                console.error(e);
                showToast('Connection error', 'error');
            }
        }

        function toggleChatSearch() {
            const actions = document.getElementById('chatHeaderActions');
            const bar = document.getElementById('chatSearchBar');
            const input = document.getElementById('chatSearchInput');
            
            if (bar.classList.contains('hidden')) {
                actions.classList.add('hidden');
                bar.classList.remove('hidden');
                input.focus();
            } else {
                actions.classList.remove('hidden');
                bar.classList.add('hidden');
                input.value = '';
                filterMessages();
            }
        }

        function filterMessages() {
            const query = document.getElementById('chatSearchInput').value.toLowerCase();
            document.querySelectorAll('.message-wrap').forEach(msg => {
                const content = msg.getAttribute('data-content');
                if (content.includes(query)) {
                    msg.classList.remove('hidden');
                } else {
                    msg.classList.add('hidden');
                }
            });
        }

        // Close dropdowns on outside click
        window.onclick = function(event) {
            if (!event.target.matches('.material-symbols-outlined')) {
                document.getElementById('sidebarMenu').classList.remove('show');
            }
        }
    </script>
</body>
</html>
