@extends('layouts.app')

@section('title', 'Register Transaction - CashbackFlow')

@section('content')
<div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
    <div class="glass-card animate-fade-in" style="width: 100%; max-width: 500px; padding: 40px;">
        <h2 style="font-size: 1.8rem; margin-bottom: 8px;" class="gradient-text">New Expense</h2>
        <p style="opacity: 0.6; font-size: 0.9rem; margin-bottom: 32px;">Record your transaction and let us categorize it for you.</p>

        <form id="transaction-form" style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-size: 0.85rem; font-weight: 500; opacity: 0.8;">Description</label>
                <input type="text" id="description" name="description" class="input-field" placeholder="e.g. Uber Ride, Mercado Central..." required>
            </div>

            <div style="display: flex; gap: 16px;">
                <div style="display: flex; flex-direction: column; gap: 8px; flex: 1;">
                    <label style="font-size: 0.85rem; font-weight: 500; opacity: 0.8;">Amount (BRL)</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" class="input-field" placeholder="0.00" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px; flex: 1;">
                    <label style="font-size: 0.85rem; font-weight: 500; opacity: 0.8;">Date</label>
                    <input type="date" id="date" name="date" class="input-field" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 12px; width: 100%;">
                <span>Register Transaction</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
            </button>
        </form>

        <div id="status-message" style="margin-top: 20px; padding: 12px; border-radius: 12px; font-size: 0.9rem; display: none;"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('transaction-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const statusDiv = document.getElementById('status-message');
        
        // Initial state
        submitBtn.disabled = true;
        submitBtn.querySelector('span').textContent = 'Processing...';
        statusDiv.style.display = 'none';
        
        try {
            const formData = {
                description: document.getElementById('description').value,
                amount: parseFloat(document.getElementById('amount').value),
                date: document.getElementById('date').value,
            };

            const response = await fetch('/api/transactions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (response.ok) {
                statusDiv.textContent = '✨ Transaction registered successfully!';
                statusDiv.style.background = 'rgba(34, 197, 94, 0.1)';
                statusDiv.style.color = '#4ade80';
                statusDiv.style.display = 'block';
                form.reset();
                document.getElementById('date').value = new Date().toISOString().split('T')[0];
            } else {
                throw new Error(result.message || 'Validation failed');
            }
        } catch (error) {
            statusDiv.textContent = '❌ ' + error.message;
            statusDiv.style.background = 'rgba(239, 68, 68, 0.1)';
            statusDiv.style.color = '#f87171';
            statusDiv.style.display = 'block';
        } finally {
            submitBtn.disabled = false;
            submitBtn.querySelector('span').textContent = 'Register Transaction';
        }
    });
</script>
@endsection
