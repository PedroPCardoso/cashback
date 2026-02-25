@extends('layouts.app')

@section('title', 'Monthly Summary - CashbackFlow')

@section('content')
<div class="animate-fade-in" style="padding-top: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 48px;">
        <div>
            <h1 style="font-size: 2.5rem; margin-bottom: 8px;" class="gradient-text">Financial Recovery</h1>
            <p style="opacity: 0.6;">Your cashback performance for the month.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <!-- Simple month selector -->
            <select id="month-select" class="input-field" style="width: 140px;">
                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $i => $m)
                    <option value="{{ $i + 1 }}" {{ ($i + 1) == date('m') ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            <input type="number" id="year-select" class="input-field" style="width: 100px;" value="{{ date('Y') }}">
        </div>
    </div>

    <!-- Stats Overview -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 48px;">
        <div class="glass-card" style="padding: 40px; text-align: center;">
            <p style="font-size: 0.9rem; opacity: 0.6; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 2px;">Total Spent</p>
            <h2 id="total-spent" style="font-size: 3rem; font-weight: 700;">R$ 0.00</h2>
        </div>
        <div class="glass-card" style="padding: 40px; text-align: center; border-color: rgba(74, 222, 128, 0.3);">
            <p style="font-size: 0.9rem; opacity: 0.6; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 2px;">Cashback Earned</p>
            <h2 id="total-cashback" style="font-size: 3rem; font-weight: 700; color: #4ade80;">R$ 0.00</h2>
        </div>
    </div>

    <!-- Category Breakdown -->
    <h3 style="font-size: 1.5rem; margin-bottom: 24px;">Category Breakdown</h3>
    <div id="category-breakdown" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px;">
        <!-- Categories will be loaded here -->
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadSummary() {
        const year = document.getElementById('year-select').value;
        const month = document.getElementById('month-select').value;
        
        const response = await fetch(`/api/summary/${year}/${month}`);
        const data = await response.json();
        
        document.getElementById('total-spent').textContent = `R$ ${data.total_spent.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
        document.getElementById('total-cashback').textContent = `R$ ${data.total_cashback.toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
        
        const grid = document.getElementById('category-breakdown');
        if (data.categories.length === 0) {
            grid.innerHTML = '<div style="grid-column: 1/-1; padding: 40px; text-align: center; opacity: 0.5;">No transactions recorded for this period.</div>';
            return;
        }

        grid.innerHTML = data.categories.map(cat => `
            <div class="glass-card" style="padding: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h4 style="font-size: 1.1rem; opacity: 0.9;">${cat.category_name}</h4>
                    <span style="font-size: 0.7rem; padding: 2px 8px; border-radius: 4px; background: ${cat.status === 'exceeded' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(34, 197, 94, 0.1)'}; color: ${cat.status === 'exceeded' ? '#f87171' : '#4ade80'};">
                        ${cat.status === 'exceeded' ? 'LIMIT EXCEEDED' : 'WITHIN LIMIT'}
                    </span>
                </div>
                
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 8px;">
                    <span style="opacity: 0.6;">Spent</span>
                    <span>R$ ${cat.total_spent.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                    <span style="opacity: 0.6;">Cashback</span>
                    <span style="color: #4ade80;">+ R$ ${cat.cashback_earned.toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span>
                </div>
            </div>
        `).join('');
    }

    document.getElementById('month-select').addEventListener('change', loadSummary);
    document.getElementById('year-select').addEventListener('change', loadSummary);

    loadSummary();
</script>
@endsection
