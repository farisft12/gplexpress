<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationTemplateController extends Controller
{
    /**
     * List all templates (Super Admin only)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403, 'Only Super Admin can manage templates');
        }

        $templates = MessageTemplate::with('branch')
            ->orderBy('channel')
            ->orderBy('code')
            ->paginate(20);

        return view('admin.notification-templates.index', compact('templates'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $branches = \App\Models\Branch::all();
        $channels = ['whatsapp', 'email', 'sms'];
        $availableVariables = $this->getAvailableVariables();

        return view('admin.notification-templates.create', compact('branches', 'channels', 'availableVariables'));
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:message_templates,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'channel' => ['required', 'in:whatsapp,email,sms'],
            'content' => ['required', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['boolean'],
            'variables' => ['nullable', 'array'],
        ]);

        $template = MessageTemplate::create($validated);

        return redirect()
            ->route('admin.notification-templates.index')
            ->with('success', 'Template berhasil dibuat');
    }

    /**
     * Show edit form
     */
    public function edit(MessageTemplate $notificationTemplate)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $branches = \App\Models\Branch::all();
        $channels = ['whatsapp', 'email', 'sms'];
        $availableVariables = $this->getAvailableVariables();

        return view('admin.notification-templates.edit', compact('notificationTemplate', 'branches', 'channels', 'availableVariables'));
    }

    /**
     * Update template
     */
    public function update(Request $request, MessageTemplate $notificationTemplate)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'unique:message_templates,code,' . $notificationTemplate->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'channel' => ['required', 'in:whatsapp,email,sms'],
            'content' => ['required', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'is_active' => ['boolean'],
            'variables' => ['nullable', 'array'],
        ]);

        $notificationTemplate->update($validated);

        return redirect()
            ->route('admin.notification-templates.index')
            ->with('success', 'Template berhasil diperbarui');
    }

    /**
     * Delete template
     */
    public function destroy(MessageTemplate $notificationTemplate)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin()) {
            abort(403);
        }

        $notificationTemplate->delete();

        return redirect()
            ->route('admin.notification-templates.index')
            ->with('success', 'Template berhasil dihapus');
    }

    /**
     * Get available variables for templates
     */
    protected function getAvailableVariables(): array
    {
        return [
            'resi' => 'Nomor resi',
            'status' => 'Status pengiriman',
            'eta' => 'Estimasi waktu tiba',
            'amount' => 'Jumlah COD (jika COD)',
            'receiver_name' => 'Nama penerima',
            'receiver_phone' => 'Nomor telepon penerima (masked)',
            'sender_name' => 'Nama pengirim',
            'branch_name' => 'Nama cabang',
            'sla_status' => 'Status SLA',
            'courier_name' => 'Nama kurir',
        ];
    }
}
