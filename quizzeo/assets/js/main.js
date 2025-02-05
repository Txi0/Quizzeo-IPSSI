let questionCount = 0;

function addQuestion() {
    const container = document.getElementById('questions-container');
    const questionBlock = document.createElement('div');
    questionBlock.className = 'question-block';

    questionBlock.innerHTML = `
        <div class="form-group">
            <label for="question-${questionCount}">Question ${questionCount + 1}</label>
            <input type="text" id="question-${questionCount}" name="questions[${questionCount}][texte]" required>
        </div>

        <div class="form-group">
            <label for="type-${questionCount}">Type de réponse</label>
            <select id="type-${questionCount}" name="questions[${questionCount}][type]" onchange="handleQuestionType(this, ${questionCount})">
                <option value="rating">Note de satisfaction (1-5)</option>
                <option value="qcm">Choix multiples</option>
                <option value="text">Réponse libre</option>
            </select>
        </div>

        <div id="options-container-${questionCount}" class="options-container"></div>

        <button type="button" class="btn-danger" onclick="removeQuestion(this)">Supprimer la question</button>
    `;

    container.appendChild(questionBlock);
    questionCount++;
}

function handleQuestionType(select, index) {
    const selectedType = select.value;
    const optionsContainer = document.getElementById(`options-container-${index}`);
    optionsContainer.innerHTML = '';

    if (selectedType === 'qcm') {
        const optionInput = `
            <div class="form-group">
                <label>Option 1</label>
                <input type="text" name="questions[${index}][options][]" required>
            </div>
            <button type="button" class="btn-secondary" onclick="addOption(${index})">Ajouter une option</button>
        `;
        optionsContainer.innerHTML = optionInput;
    }
}

function addOption(index) {
    const optionsContainer = document.getElementById(`options-container-${index}`);
    const optionCount = optionsContainer.querySelectorAll('.form-group').length + 1;

    const newOption = document.createElement('div');
    newOption.className = 'form-group';
    newOption.innerHTML = `
        <label>Option ${optionCount}</label>
        <input type="text" name="questions[${index}][options][]" required>
    `;

    optionsContainer.insertBefore(newOption, optionsContainer.lastElementChild);
}

function removeQuestion(button) {
    button.parentElement.remove();
}
