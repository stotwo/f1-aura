document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('suggestionModal');
    const modalList = document.getElementById('suggestionList');
    const modalMessage = document.getElementById('suggestionMessage');
    const btnAccept = document.getElementById('btnAccept');
    const btnRefuse = document.getElementById('btnRefuse');
    const btnClose = document.querySelector('.close-modal');
    
    let currentSuggestions = [];


    function showModal(title, suggestions) {
        if (!modal) return;
        
        modalMessage.innerHTML = title;
        modalList.innerHTML = '';
        currentSuggestions = suggestions;

        modalList.className = 'suggestion-grid';

        suggestions.forEach((item, index) => {
            const div = document.createElement('div');
            div.className = 'suggestion-card selected';
            
            div.innerHTML = `
                <input type="checkbox" id="suggestion_check_${index}" checked style="display:none;">
                <div class="suggestion-image-container" style="background-color: ${item.color || '#e0e0e0'};">
                    <img src="${item.image}" alt="" class="${item.type}-img">
                    <div class="check-overlay">✓</div>
                </div>
                <div class="suggestion-name">${item.label.replace(/<[^>]*>/g, '')}</div>
            `;
            
            div.addEventListener('click', function() {
                const checkbox = this.querySelector('input');
                checkbox.checked = !checkbox.checked;
                
                if (checkbox.checked) {
                    this.classList.add('selected');
                } else {
                    this.classList.remove('selected');
                }
            });

            modalList.appendChild(div);
        });

        modal.classList.add('active');
        modal.style.visibility = 'visible';
        modal.style.opacity = '1';
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('active');
        modal.style.visibility = 'hidden';
        modal.style.opacity = '0';
        currentSuggestions = [];
    }

    function triggerChange(element) {
        if (!element.checked) {
            element.checked = true;
            const card = element.closest('.selection-card');
            if (card) card.classList.add('selected');
        }
    }

    function getImageSrc(inputElement) {
        const card = inputElement.closest('.selection-card');
        const img = card.querySelector('.selection-image');
        return img ? img.src : '';
    }


    if (btnAccept) btnAccept.addEventListener('click', () => {
        currentSuggestions.forEach((item, index) => {
            const checkbox = document.getElementById(`suggestion_check_${index}`);
            if (checkbox && checkbox.checked) {
                triggerChange(item.input);
            }
        });
        closeModal();
    });

    if (btnRefuse) btnRefuse.addEventListener('click', closeModal);
    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (modal) modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });


    document.body.addEventListener('change', function(e) {
        if (e.target.matches('.hidden-checkbox') && e.isTrusted && e.target.checked) {
            
            const card = e.target.closest('.selection-card');
            if (!card) return;
            
            let suggestions = [];
            let headerMessage = '';

                const getTeamColor = (element) => {
                    const card = element.closest('.selection-card');
                    return card ? getComputedStyle(card).getPropertyValue('--team-color').trim() : '#f0f0f0';
                };

                if (card.classList.contains('driver-card')) {
                    const teamId = card.dataset.teamId;
                    const driverName = card.dataset.driverName;
                    
                    headerMessage = `Vous avez sélectionné <strong>${driverName}</strong>.<br>Complétez votre sélection avec :`;

                    const teamInput = document.querySelector(`.team-card input[value="${teamId}"]`);
                    if (teamInput && !teamInput.checked) {
                        const teamName = teamInput.closest('.team-card').dataset.teamName;
                        suggestions.push({
                            label: `L'écurie <strong>${teamName}</strong>`,
                            input: teamInput,
                            image: getImageSrc(teamInput),
                            type: 'team',
                            color: getTeamColor(teamInput)
                        });
                    }

                    const teammateInputs = Array.from(document.querySelectorAll(`.driver-card[data-team-id="${teamId}"] input`))
                                           .filter(inp => inp.value !== e.target.value && !inp.checked);
                    
                    teammateInputs.forEach(inp => {
                        const teammateName = inp.closest('.driver-card').dataset.driverName;
                        suggestions.push({
                            label: `Son coéquipier <strong>${teammateName}</strong>`,
                            input: inp,
                            image: getImageSrc(inp),
                            type: 'driver',
                            color: '#e0e0e0'
                        });
                    });
                }
                
                else if (card.classList.contains('team-card')) {
                    const teamId = e.target.value;
                    const teamName = card.dataset.teamName;
                    const teamColor = getTeamColor(card);
                    
                    headerMessage = `Vous avez sélectionné <strong>${teamName}</strong>.<br>Ajoutez les pilotes officiels :`;

                    const driverInputs = Array.from(document.querySelectorAll(`.driver-card[data-team-id="${teamId}"] input`))
                                         .filter(inp => !inp.checked);
                    
                    driverInputs.forEach(inp => {
                        const driverName = inp.closest('.driver-card').dataset.driverName;
                        suggestions.push({
                            label: `<strong>${driverName}</strong>`,
                            input: inp,
                            image: getImageSrc(inp),
                            type: 'driver',
                            color: '#e0e0e0'
                        });
                    });
                }

            if (suggestions.length > 0) {
                showModal(headerMessage, suggestions);
            }
        }
    });
});
