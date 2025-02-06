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

// Ajouter dans la partie script
document.getElementById('quizForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validation du formulaire
    const titre = document.getElementById('titre').value.trim();
    if (!titre) {
        alert('Le titre du quiz est obligatoire');
        return;
    }

    const questions = document.querySelectorAll('.question-block');
    if (questions.length === 0) {
        alert('Vous devez ajouter au moins une question');
        return;
    }

    // Vérification des questions QCM
    let isValid = true;
    questions.forEach((question, index) => {
        const typeSelect = question.querySelector('select[name^="questions"][name$="[type]"]');
        if (typeSelect.value === 'qcm') {
            const options = question.querySelectorAll('input[name^="questions"][name$="[options][]"]');
            const reponseCorrect = question.querySelector('input[type="radio"]:checked');
            
            if (options.length < 2) {
                alert(`La question ${index + 1} doit avoir au moins 2 options`);
                isValid = false;
            }
            if (!reponseCorrect) {
                alert(`Veuillez sélectionner une réponse correcte pour la question ${index + 1}`);
                isValid = false;
            }
        }
    });

    if (isValid) {
        this.submit();
    }
});
