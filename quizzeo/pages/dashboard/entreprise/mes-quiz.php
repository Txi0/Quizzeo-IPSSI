<table class="quiz-table">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Statut</th>
            <th>Réponses</th>
            <th>Date de création</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($entrepriseQuizzes as $quiz): ?>
        <tr>
            <td>
                <div class="quiz-title">
                    <?php echo htmlspecialchars($quiz['titre']); ?>
                    <?php if (!empty($quiz['description'])): ?>
                        <span class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></span>
                    <?php endif; ?>
                </div>
            </td>
            <td>
                <span class="status-badge <?php echo $quiz['status']; ?>">
                    <?php echo ucfirst($quiz['status']); ?>
                </span>
            </td>
            <td class="text-center"><?php echo $quiz['nb_reponses']; ?></td>
            <td><?php echo date('d/m/Y', strtotime($quiz['created_at'])); ?></td>
            <td class="actions">
                <?php if ($quiz['status'] === 'en cours d\'écriture'): ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                        <input type="hidden" name="action" value="lancer">
                        <button type="submit" class="btn-success btn-small">Lancer</button>
                    </form>
                    <a href="edit-quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-secondary btn-small">
                        Modifier
                    </a>
                <?php elseif ($quiz['status'] === 'lancé'): ?>
                    <?php
                        // Génération du lien de partage en utilisant l'ID du quiz
                        $shareLink = "http://" . $_SERVER['HTTP_HOST'] . "/repondre.php?id=" . $quiz['id'];
                        echo '<div class="share-link-container">
                                <input type="text" 
                                    value="' . htmlspecialchars($shareLink) . '" 
                                    id="shareLink_' . $quiz['id'] . '" 
                                    readonly 
                                    class="share-link">
                                <button onclick="copyLink(\'' . $quiz['id'] . '\')" class="btn-secondary btn-small">
                                    Copier le lien
                                </button>
                            </div>';
                    ?>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="quiz_id" value="<?php echo $quiz['id']; ?>">
                        <input type="hidden" name="action" value="terminer">
                        <button type="submit" class="btn-warning btn-small">Terminer</button>
                    </form>
                <?php endif; ?>
                
                <?php if ($quiz['status'] !== 'en cours d\'écriture'): ?>
                    <a href="analyse.php?id=<?php echo $quiz['id']; ?>" class="btn-primary btn-small">
                        Voir les résultats
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
