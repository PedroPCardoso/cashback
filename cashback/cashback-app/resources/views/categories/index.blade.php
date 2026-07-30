@extends('layouts.app')

@section('title', 'Categories - CashbackFlow')

@section('content')
<div class="animate-fade-in" style="padding-top: 40px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <div>
            <h1 style="font-size: 2rem;" class="gradient-text">Category Settings</h1>
            <p style="opacity: 0.6;">Configure rules to maximize your cashback rewards.</p>
        </div>
    </div>

    <div id="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 24px;">
        <!-- Categories will be loaded here -->
        <div style="padding: 40px; text-align: center; opacity: 0.5; grid-column: 1 / -1;">Loading categories...</div>
    </div>
</div>

<!-- Edit Modal -->
<div id="edit-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: center; padding: 24px;">
    <div class="glass-card" style="width: 100%; max-width: 600px; padding: 40px; position: relative;">
        <button onclick="closeModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: white; cursor: pointer; opacity: 0.6;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
        
        <h2 id="modal-title" style="margin-bottom: 32px;" class="gradient-text">Edit Category</h2>
        
        <form id="edit-form" style="display: flex; flex-direction: column; gap: 20px;">
            <input type="hidden" id="edit-id">
            
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-size: 0.85rem; font-weight: 500; opacity: 0.8;">Name</label>
                <input type="text" id="edit-name" class="input-field" required>
            </div>

            <div style="display: flex; gap: 16px;">
                <div style="display: flex; flex-direction: column; gap: 8px; flex: 1;">
                    <label style="font-size: 0.85rem; font-weight: 500; opacity: 0.8;">Cashback Rate (%)</label>
                    <input type="number" id="edit-rate" step="0.1" min="0" max="100" class="input-field" required>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px; flex: 1;">
                    <label style="font-size: 0.85rem; font-weight: 500; opacity: 0.8;">Monthly Limit (BRL)</label>
                    <input type="number" id="edit-limit" step="0.01" min="0" class="input-field" placeholder="Unlimited">
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-size: 0.85rem; font-weight: 500; opacity: 0.8;">Keywords (comma separated)</label>
                <textarea id="edit-keywords" class="input-field" style="min-height: 80px; resize: vertical;"></textarea>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 12px;">Save Changes</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let categories = [];

    async function loadCategories() {
        const response = await fetch('/api/categories');
        categories = await response.json();
        renderCategories();
    }

    function renderCategories() {
        const grid = document.getElementById('categories-grid');
        grid.innerHTML = categories.map(cat => `
            <div class="glass-card" style="padding: 32px; display: flex; flex-direction: column; gap: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <h3 style="font-size: 1.4rem; margin-bottom: 4px;">${cat.name}</h3>
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.5; padding: 2px 8px; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px;">${cat.type}</span>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: #4ade80;">${cat.cashback_rate}%</div>
                        <div style="font-size: 0.75rem; opacity: 0.6;">Cashback</div>
                    </div>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 12px; flex-grow: 1;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                        <span style="opacity: 0.6;">Monthly Limit</span>
                        <span>${cat.monthly_limit ? 'R$ ' + cat.monthly_limit.toFixed(2) : 'Unlimited'}</span>
                    </div>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
                        ${cat.keywords.map(k => `
                            <span style="font-size: 0.75rem; background: rgba(99, 102, 241, 0.1); color: #818cf8; padding: 4px 10px; border-radius: 20px; border: 1px solid rgba(99, 102, 241, 0.2);">
                                ${k.keyword}
                            </span>
                        `).join('')}
                    </div>
                </div>

                <button onclick="openModal('${cat.id}')" class="btn-primary" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; width: 100%;">
                    Edit Configuration
                </button>
            </div>
        `).join('');
    }

    function openModal(id) {
        const cat = categories.find(c => c.id === id);
        if (!cat) return;

        document.getElementById('edit-id').value = cat.id;
        document.getElementById('edit-name').value = cat.name;
        document.getElementById('edit-rate').value = cat.cashback_rate;
        document.getElementById('edit-limit').value = cat.monthly_limit || '';
        document.getElementById('edit-keywords').value = cat.keywords.map(k => k.keyword).join(', ');
        
        document.getElementById('modal-title').textContent = `Edit ${cat.name}`;
        document.getElementById('edit-modal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('edit-modal').style.display = 'none';
    }

    document.getElementById('edit-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const submitBtn = e.target.querySelector('button[type="submit"]');
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';

        try {
            const keywords = document.getElementById('edit-keywords').value
                .split(',')
                .map(k => k.trim())
                .filter(k => k.length > 0);

            const data = {
                name: document.getElementById('edit-name').value,
                cashback_rate: parseFloat(document.getElementById('edit-rate').value),
                monthly_limit: document.getElementById('edit-limit').value ? parseFloat(document.getElementById('edit-limit').value) : null,
                keywords: keywords
            };

            const response = await fetch(`/api/categories/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                closeModal();
                await loadCategories();
            } else {
                const res = await response.json();
                alert('Error: ' + res.message);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Save Changes';
        }
    });

    // Close modal on click outside
    document.getElementById('edit-modal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('edit-modal')) closeModal();
    });

    loadCategories();
</script>
@endsection
