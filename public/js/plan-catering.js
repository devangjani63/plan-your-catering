/**
 * Plan Your Catering
 * Stage 14 – Steps + Validation + Modal + Selection (FIXED)
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
      
      const ADD_ONS = [
      { key: 'live_counter', label: 'Live Food Counter', type: 'per_guest', price: 150 },
      { key: 'jain_food', label: 'Jain Food Setup', type: 'per_guest', price: 80 },
      { key: 'staff', label: 'Extra Service Staff', type: 'per_guest', price: 50 },
      { key: 'cutlery', label: 'Premium Cutlery', type: 'per_guest', price: 40 },
      { key: 'decor', label: 'Decoration', type: 'flat', price: 25000 }
        ];

    const selectedAddons = new Set();

      
    const container = document.getElementById('pyc-plan-catering');
    if (!container) return;

    /* -----------------------------
       STEP NAVIGATION
    ----------------------------- */
    const steps = Array.from(container.querySelectorAll('.pyc-step'));
    const indicator = container.querySelector('.pyc-step-indicator span');
    const nextBtn = container.querySelector('.pyc-btn-primary');
    const backBtn = container.querySelector('.pyc-btn-secondary');

    let currentStep = 0;

    function validateStep(stepEl) {
      const required = stepEl.querySelectorAll('[data-required="1"]');

      for (let field of required) {
        const value = field.value.trim();
        if (!value) {
          alert('Please fill all required fields.');
          field.focus();
          return false;
        }
        
        if (field.name === 'guests') {
          const g = parseInt(value, 10);
          if (isNaN(g) || g < 1 || g > 5000) {
            alert('Guest count must be between 1 and 5000.');
            field.focus();
            return false;
          }
        }
        
        if (field.name === 'event_date') {
          const selected = new Date(value);
          const today = new Date();
          today.setHours(0,0,0,0);
        
          if (selected < today) {
            alert('Event date cannot be in the past.');
            field.focus();
            return false;
          }
        }

        if (field.name === 'customer_phone') {
          const phone = field.value.replace(/\D/g, '');
          const countrySelect = container.querySelector('[data-country-select]');
          const selected = countrySelect.options[countrySelect.selectedIndex];
          const requiredDigits = parseInt(selected.dataset.digits, 10);
        
          if (phone.length !== requiredDigits) {
            alert(`Phone number must be exactly ${requiredDigits} digits for ${selected.text}`);
            field.focus();
            return false;
          }
        }
        
        if (field.name === 'customer_email') {
         const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
          if (!ok) {
            alert('Enter a valid email address.');
            field.focus();
            return false;
          }
        }
      }
      return true;
    }

    function updateSteps() {
      steps.forEach((step, index) => {
        step.style.display = index === currentStep ? 'block' : 'none';
      });

      indicator.textContent = String(currentStep + 1);
      backBtn.style.display = currentStep === 0 ? 'none' : 'block';
      nextBtn.textContent =
        currentStep === steps.length - 1 ? 'Submit' : 'Next';
    }

    nextBtn.addEventListener('click', function () {
      if (!validateStep(steps[currentStep])) return;
    
      // FINAL STEP → SUBMIT
      if (currentStep === steps.length - 1) {
        submitEnquiry();
        return;
      }
    
      currentStep++;
      updateSteps();
    
      const stepEl = steps[currentStep];
        if (!stepEl) return;
        
        const stepNo = Number(stepEl.dataset.step);
        
        switch (stepNo) {
          case 4:
            renderMenuSummary();
            break;
        
          case 5:
            renderAddons();
            break;
        
          case 7:
            renderEstimate();
            break;
        }
    });


    backBtn.addEventListener('click', function () {
      if (currentStep > 0) {
        currentStep--;
        updateSteps();
      }
    });

    updateSteps();

    /* -----------------------------
       MENU MODAL + SELECTION
    ----------------------------- */
    if (typeof PYC_MENU_ITEMS === 'undefined') return;

    const modal = document.getElementById('pyc-menu-modal');
    const modalTitle = document.getElementById('pyc-modal-title');
    const modalItems = document.getElementById('pyc-modal-items');
    const closeBtn = modal.querySelector('.pyc-modal-close');

    const selectionState = {}; // { sectionId: Set(itemId) }

    function ensureSection(sectionId) {
      if (!selectionState[sectionId]) {
        selectionState[sectionId] = new Set();
      }
    }

    function updateSectionButtonCount(sectionId) {
      const btn = document.querySelector(
        '.pyc-menu-section-btn[data-section-id="' + sectionId + '"]'
      );
      if (!btn) return;

      const base = btn.getAttribute('data-base') || btn.textContent;
      btn.setAttribute('data-base', base);

      const count = selectionState[sectionId].size;
      btn.textContent = count ? `${base} (${count} selected)` : base;
    }

    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.pyc-menu-section-btn');
      if (!btn) return;

      const sectionId = btn.dataset.sectionId;
      const sectionName =
        btn.getAttribute('data-base') || btn.textContent;

      ensureSection(sectionId);

      modalTitle.textContent = sectionName;
      modalItems.innerHTML = '';

      const items = PYC_MENU_ITEMS[sectionId] || [];

      if (!items.length) {
        modalItems.innerHTML =
          '<li class="pyc-debug">No items available.</li>';
      } else {
        items.forEach(item => {
          const li = document.createElement('li');
          li.className = 'pyc-item-row';

          const checked = selectionState[sectionId].has(String(item.id));

          li.innerHTML = `
            <label class="pyc-item-label">
              <input type="checkbox" data-item-id="${item.id}" ${
            checked ? 'checked' : ''
          }>
              <span>${item.title}</span>
            </label>
          `;
          modalItems.appendChild(li);
        });
      }

      modal.classList.add('pyc-modal-open');
      modal.setAttribute('aria-hidden', 'false');
    });

    modalItems.addEventListener('change', function (e) {
      const cb = e.target;
      if (cb.tagName !== 'INPUT') return;

      const sectionId =
        document
          .querySelector('.pyc-modal-open')
          ?.previousElementSibling || null;

      const itemId = cb.dataset.itemId;

      Object.keys(selectionState).forEach(sid => {
        if (selectionState[sid].has(itemId)) {
          selectionState[sid].delete(itemId);
        }
      });

      const currentSectionId = Object.keys(PYC_MENU_ITEMS).find(id =>
        PYC_MENU_ITEMS[id].some(i => String(i.id) === itemId)
      );

      ensureSection(currentSectionId);

      cb.checked
        ? selectionState[currentSectionId].add(itemId)
        : selectionState[currentSectionId].delete(itemId);

      updateSectionButtonCount(currentSectionId);
    });

    closeBtn.addEventListener('click', function () {
      modal.classList.remove('pyc-modal-open');
      modal.setAttribute('aria-hidden', 'true');
    });
    
    function renderMenuSummary() {
  const summaryBox = document.getElementById('pyc-menu-summary');
  if (!summaryBox) return;

  summaryBox.innerHTML = '';

  const sectionIds = Object.keys(selectionState).filter(
    id => selectionState[id].size > 0
  );

  if (!sectionIds.length) {
    summaryBox.innerHTML =
      '<p class="pyc-debug">No menu items selected.</p>';
    return;
  }

  sectionIds.forEach(sectionId => {
    const sectionBtn = document.querySelector(
      '.pyc-menu-section-btn[data-section-id="' + sectionId + '"]'
    );

    const sectionName =
      sectionBtn?.getAttribute('data-base') || sectionBtn?.textContent || '';

    const sectionBlock = document.createElement('div');
    sectionBlock.className = 'pyc-summary-section';

    const title = document.createElement('h4');
    title.textContent = sectionName;
    sectionBlock.appendChild(title);

    const ul = document.createElement('ul');

    selectionState[sectionId].forEach(itemId => {
      const item = (PYC_MENU_ITEMS[sectionId] || []).find(
        i => String(i.id) === String(itemId)
      );
      if (!item) return;

      const li = document.createElement('li');
      li.textContent = item.title;
      ul.appendChild(li);
    });

    sectionBlock.appendChild(ul);
    summaryBox.appendChild(sectionBlock);
  });
}

    function renderAddons() {
        console.log(PYC_MENU_ITEMS);
      const box = document.getElementById('pyc-addons');
      if (!box) return;
    
      box.innerHTML = '';
    
      ADD_ONS.forEach(addon => {
        const label = document.createElement('label');
        label.className = 'pyc-addon';
    
        const checked = selectedAddons.has(addon.key);
    
        label.innerHTML = `
          <input type="checkbox" data-addon="${addon.key}" ${checked ? 'checked' : ''}>
          <span>
            ${addon.label}
            <small>
              (${addon.type === 'per_guest'
                ? `₹${addon.price} / guest`
                : `₹${addon.price.toLocaleString()} flat`
              })
            </small>
          </span>
        `;
    
        box.appendChild(label);
      });
    }

    document.addEventListener('change', function (e) {
      const cb = e.target;
      if (!cb.matches('[data-addon]')) return;
    
      const key = cb.getAttribute('data-addon');
    
      cb.checked ? selectedAddons.add(key) : selectedAddons.delete(key);
    });

    const ESTIMATE_CONFIG = {
      basePricePerGuest: 650,
      itemWeightCost: 35, // medium default
      minBuffer: 0.9,
      maxBuffer: 1.15
    };
    
    const ITEM_WEIGHT_COST = {
      '1': 20,  // Simple (drinks, soup)
      '2': 40,  // Medium (starters, sabzi)
      '3': 80   // Heavy (paneer tikka, specials)
    };


    function calculateEstimate() {
  const guestsInput = container.querySelector('input[name="guests"]');
  const guests = guestsInput ? parseInt(guestsInput.value, 10) : 0;
  if (!guests) return null;

  let selectedItemCount = 0;
  Object.values(selectionState).forEach(set => {
    selectedItemCount += set.size;
  });
  
  
  let menuItemCostPerGuest = 0;

    Object.keys(selectionState).forEach(sectionId => {
      selectionState[sectionId].forEach(itemId => {
        const item = (PYC_MENU_ITEMS[sectionId] || []).find(
          i => String(i.id) === String(itemId)
        );
        if (!item) return;
    
        const weightKey = String(item.weight || '2');
        menuItemCostPerGuest += ITEM_WEIGHT_COST[weightKey] || 40;
      });
    });

  let perGuestAddonCost = 0;
  let flatAddonCost = 0;

  ADD_ONS.forEach(addon => {
    if (!selectedAddons.has(addon.key)) return;

    if (addon.type === 'per_guest') {
      perGuestAddonCost += addon.price;
    } else {
      flatAddonCost += addon.price;
    }
  });

  const perGuestTotal =
    ESTIMATE_CONFIG.basePricePerGuest +
    menuItemCostPerGuest +
    perGuestAddonCost;

  const baseTotal = perGuestTotal * guests + flatAddonCost;
      console.log('Estimate debug:', {
      guests,
      menuItemCostPerGuest,
      selectedItems: Object.values(selectionState)
  .reduce((sum, set) => sum + set.size, 0)
    });

  return {
    guests,
    selectedItemCount,
    perGuestTotal,
    baseTotal,
    min: Math.round(baseTotal * ESTIMATE_CONFIG.minBuffer),
    max: Math.round(baseTotal * ESTIMATE_CONFIG.maxBuffer)
  };
  
}

function renderEstimate() {
    console.log('renderEstimate() CALLED');
  const box = document.getElementById('pyc-estimate-box');
  if (!box) return;

  const estimate = calculateEstimate();
  if (!estimate) {
    box.innerHTML = '<p class="pyc-debug">Incomplete data.</p>';
    return;
  }

  box.innerHTML = `
    <div class="pyc-estimate-line">Guests: <strong>${estimate.guests}</strong></div>
    <div class="pyc-estimate-line">Menu items selected: <strong>${estimate.selectedItemCount}</strong></div>
    <div class="pyc-estimate-line">Approx. per guest: <strong>₹${estimate.perGuestTotal}</strong></div>

    <hr>

    <div class="pyc-estimate-total">
      ₹${estimate.min.toLocaleString()} – ₹${estimate.max.toLocaleString()}
    </div>
  `;
}

function submitEnquiry() {
  const estimate = calculateEstimate();
  if (!estimate) {
    alert('Estimate could not be calculated.');
    return;
  }

  const payload = {
  action: 'pyc_submit_enquiry',

  // Event details
  occasion: container.querySelector('[name="occasion"]').value,
  event_date: container.querySelector('[name="event_date"]').value,
  event_city: container.querySelector('[name="event_city"]').value,
  guests: estimate.guests,

  // 🔴 STEP B-2 FIX (ONLY THIS)
  customer: {
    name: container.querySelector('[name="customer_name"]').value,
    email: container.querySelector('[name="customer_email"]').value,
    phone: {
      country_code: container.querySelector('[name="country_code"]').value,
      number: container
        .querySelector('[name="customer_phone"]')
        .value.replace(/\D/g, '')
    }
  },

  // Menu snapshot
  menu: Object.keys(selectionState).map(sectionId => ({
    section_id: sectionId,
    items: Array.from(selectionState[sectionId])
  })),

  // Add-ons
  addons: Array.from(selectedAddons),

  // Estimate snapshot
  estimate: {
    per_guest: estimate.perGuestTotal,
    min: estimate.min,
    max: estimate.max
  }
};

  console.log('Submitting enquiry:', payload);

  const form = document.createElement('form');
form.method = 'POST';
form.action = pyc_vars.post_url;

// REQUIRED: action
const actionInput = document.createElement('input');
actionInput.type = 'hidden';
actionInput.name = 'action';
actionInput.value = 'pyc_submit_enquiry';
form.appendChild(actionInput);

// Payload
const payloadInput = document.createElement('input');
payloadInput.type = 'hidden';
payloadInput.name = 'pyc_payload';
payloadInput.value = JSON.stringify(payload);
form.appendChild(payloadInput);

document.body.appendChild(form);
form.submit();

}


    
  });
})();
