@extends('layouts.app')

@section('title', 'Transactions - CashbackFlow')

@section('content')
<div class="animate-fade-in" style="padding-top: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 48px;">
        <div>
            <h1 style="font-size: 2.5rem; margin-bottom: 8px;" class="gradient-text">Recent Transactions</h1>
            <p style="opacity: 0.6;">Manage and review your recorded expenses.</p>
        </div>
        <button onclick="window.location.href='/transactions/create'" class="btn primary">
            + New Transaction
        </button>
    </div>

    <div class="glass-card" style="padding: 24px;">
        <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <th style="padding: 16px; opacity: 0.6; font-weight: normal;">Date</th>
                    <th style="padding: 16px; opacity: 0.6; font-weight: normal;">Description</th>
                    <th style="padding: 16px; opacity: 0.6; font-weight: normal;">Category</th>
                    <th style="padding: 16px; opacity: 0.6; font-weight: normal; text-align: right;">Amount</th>
                    <th style="padding: 16px; opacity: 0.6; font-weight: normal; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody id="transactions-list">
                <tr>
                    <td colspan="5" style="text-align: center; padding: 48px; opacity: 0.5;">Loading transactions...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
    <div class="glass-card" style="width: 100%; max-width: 500px; padding: 40px; animation: slideUp 0.3s ease-out forwards;">
        <h3 style="font-size: 1.5rem; margin-bottom: 24px;">Edit Transaction</h3>
        
        <form id="edit-form" onsubmit="handleEditSubmit(event)">
            <input type="hidden" id="edit-id">
            
            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.9rem; opacity: 0.8;">Description</label>
                <input type="text" id="edit-description" class="input-field" required>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.9rem; opacity: 0.8;">Amount (R$)</label>
                <input type="number" id="edit-amount" class="input-field" step="0.01" min="0.01" required>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.9rem; opacity: 0.8;">Date</label>
                <input type="date" id="edit-date" class="input-field" required>
            </div>

            <div style="margin-bottom: 32px;">
                <label style="display: block; margin-bottom: 8px; font-size: 0.9rem; opacity: 0.8;">Category</label>
                <select id="edit-category" class="input-field" required>
                    <!-- Categories loaded dynamically -->
                </select>
            </div>

            <div style="display: flex; gap: 16px; justify-content: flex-end;">
                <button type="button" class="btn" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn primary" id="save-btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let categoriesList = [];

    async function loadData() {
        try {
            const [catResponse, transResponse] = await Promise.all([
                fetch('/api/categories'),
                fetch('/api/transactions')
            ]);
            
            categoriesList = await catResponse.json();
            const transactions = await transResponse.json();
            
            renderTransactions(transactions);
            populateCategorySelect();
        } catch (error) {
            console.error('Error loading data:', error);
            document.getElementById('transactions-list').innerHTML = `
                <tr><td colspan="5" style="text-align: center; padding: 48px; color: #f87171;">Failed to load data.</td></tr>
            `;
        }
    }

    function renderTransactions(transactions) {
        const tbody = document.getElementById('transactions-list');
        
        if (transactions.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 48px; opacity: 0.5;">No transactions found.</td></tr>`;
            return;
        }

        tbody.innerHTML = transactions.map(t => {
            const category = categoriesList.find(c => c.id === t.category_id);
            const catName = category ? category.name : 'Unknown';
            const amountStr = (t.amount_cents / 100).toLocaleString('pt-BR', {minimumFractionDigits: 2});
            
            return `
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.02)'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 16px; opacity: 0.8;">${t.date}</td>
                    <td style="padding: 16px; font-weight: 500;">${t.description}</td>
                    <td style="padding: 16px;">
                        <span style="background: rgba(255,255,255,0.1); padding: 4px 12px; border-radius: 20px; font-size: 0.85rem;">
                            ${catName}
                        </span>
                    </td>
                    <td style="padding: 16px; text-align: right; font-weight: 500;">R$ ${amountStr}</td>
                    <td style="padding: 16px; text-align: right;">
                        <button onclick="openEditModal('${t.id}', '${t.description}', ${t.amount_cents}, '${t.date}', '${t.category_id}')" style="background: transparent; border: none; color: #a5b4fc; cursor: pointer; margin-right: 12px; font-size: 0.9rem;">Edit</button>
                        <button onclick="deleteTransaction('${t.id}')" style="background: transparent; border: none; color: #f87171; cursor: pointer; font-size: 0.9rem;">Delete</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function populateCategorySelect() {
        const select = document.getElementById('edit-category');
        select.innerHTML = categoriesList.map(c => `
            <option value="${c.id}">${c.name}</option>
        `).join('');
    }

    function openEditModal(id, desc, amountCents, date, categoryId) {
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-description').value = desc;
        document.getElementById('edit-amount').value = (amountCents / 100).toFixed(2);
        document.getElementById('edit-date').value = date;
        document.getElementById('edit-category').value = categoryId;
        
        document.getElementById('edit-modal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('edit-modal').style.display = 'none';
        document.getElementById('edit-form').reset();
    }

    async function handleEditSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('save-btn');
        const originalText = btn.textContent;
        btn.textContent = 'Saving...';
        btn.disabled = true;

        const id = document.getElementById('edit-id').value;
        const payload = {
            description: document.getElementById('edit-description').value,
            amount_cents: Math.round(parseFloat(document.getElementById('edit-amount').value) * 100),
            date: document.getElementById('edit-date').value,
            category_id: document.getElementById('edit-category').value
        };

        try {
            const res = await fetch(`/api/transactions/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeModal();
                loadData(); // reload
            } else {
                alert('Error updating transaction');
            }
        } catch (error) {
            console.error(error);
            alert('Network error');
        } finally {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    }

    async function deleteTransaction(id) {
        if (!confirm('Are you sure you want to delete this transaction? This action will reverse its cashback.')) return;

        try {
            const res = await fetch(`/api/transactions/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json' }
            });

            if (res.ok) {
                loadData(); // reload list
            } else {
                alert('Failed to delete.');
            }
        } catch (error) {
            console.error(error);
        }
    }

    // Load data on start
    loadData();
</script>
@endsection
