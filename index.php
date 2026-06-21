<?php 
include 'db.php'; 

$stmt = $pdo->query('SELECT * FROM effects ORDER BY id ASC');
$effects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Effects Library</title>
    <link rel="stylesheet" href="style.css">
    
    <?php foreach ($effects as $effect): ?>
        <style id="style-<?php echo $effect['id']; ?>">
            <?php echo $effect['css_code']; ?>
        </style>
    <?php endforeach; ?>
</head>
<body>
    <header class="header">
        <div class="container header-flex">
            <a href="index.php" class="logo">CSS<span>Craft</span></a>
            <nav class="nav">
                <a href="admin.php" class="nav-link admin-btn">Войти (Админ)</a>
            </nav>
        </div>
    </header>

    <main class="container page-content">
        
        <div id="catalog-view">
            <section class="hero">
                <h1>Коллекция CSS-эффектов</h1>
                <p>Смотрите примеры, нажимайте «Редактировать» для настройки и копирования кода.</p>
            </section>

            <div class="catalog-grid">
                <?php foreach ($effects as $row): ?>
                    <div class="effect-card">
                        <div class="card-live-demo">
                            <?php echo $row['html_markup']; ?>
                        </div>
                        
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p><?php echo htmlspecialchars($row['description']); ?></p>
                            <button class="btn-primary" onclick="openEditor(<?php echo $row['id']; ?>)">Редактировать</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="editor-view" style="display: none;">
            <div class="editor-header-info">
                <button class="btn-link" onclick="closeEditor()">&larr; Вернуться в каталог</button>
                <h2 id="editor-title">Название</h2>
            </div>

            <div class="editor-layout">
                <div class="visual-zone">
                    <div class="demo-window" id="editor-demo"></div>
                </div>

                <div class="editor-zone">
                    <div class="code-header">
                        <span>CSS Код</span>
                        <div class="editor-actions">
                            <button id="run-btn" class="btn-sm btn-run">Запустить</button>
                            <button id="copy-btn" class="btn-sm btn-copy">Копировать</button>
                        </div>
                    </div>
                    <textarea id="editor-css" spellcheck="false" autocomplete="off"></textarea>
                </div>
            </div>
        </div>

    </main>

    <script>
        const effectsData = <?php echo json_encode($effects, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS); ?>;
        
        const catalogView = document.getElementById('catalog-view');
        const editorView = document.getElementById('editor-view');
        const editorTitle = document.getElementById('editor-title');
        const editorDemo = document.getElementById('editor-demo');
        const editorCss = document.getElementById('editor-css');
        
        const runBtn = document.getElementById('run-btn');
        const copyBtn = document.getElementById('copy-btn');

        let currentActiveId = null;

        function openEditor(id) {
            let currentEffect = null;
            for (let i = 0; i < effectsData.length; i++) {
                if (effectsData[i].id == id) {
                    currentEffect = effectsData[i];
                    break;
                }
            }
            if (!currentEffect) return;

            currentActiveId = id;
editorTitle.textContent = currentEffect.title;
            editorDemo.innerHTML = currentEffect.html_markup;
            editorCss.value = currentEffect.css_code;

            catalogView.style.display = 'none';
            editorView.style.display = 'block';
            window.scrollTo(0, 0);
        }

        function closeEditor() {
            if (currentActiveId !== null) {
                let originalEffect = null;
                for (let i = 0; i < effectsData.length; i++) {
                    if (effectsData[i].id == currentActiveId) {
                        originalEffect = effectsData[i];
                        break;
                    }
                }
                
                if (originalEffect) {
                    const activeStyleTag = document.getElementById('style-' + currentActiveId);
                    if (activeStyleTag) {
                        activeStyleTag.textContent = originalEffect.css_code;
                    }
                }
            }

            catalogView.style.display = 'block';
            editorView.style.display = 'none';
            currentActiveId = null;
        }

        runBtn.addEventListener('click', function() {
            if (currentActiveId !== null) {
                const activeStyleTag = document.getElementById('style-' + currentActiveId);
                if (activeStyleTag) {
                    activeStyleTag.textContent = editorCss.value;
                }
            }
            
            runBtn.textContent = 'Готово ✓';
            runBtn.style.backgroundColor = '#16a34a';
            setTimeout(function() {
                runBtn.textContent = 'Запустить';
                runBtn.style.backgroundColor = '';
            }, 1500);
        });

        copyBtn.addEventListener('click', function() {
            editorCss.select();
            try {
                document.execCommand('copy');
                copyBtn.textContent = 'Скопировано ✓';
                copyBtn.style.backgroundColor = '#2563eb';
                setTimeout(function() {
                    copyBtn.textContent = 'Копировать';
                    copyBtn.style.backgroundColor = '';
                }, 2000);
            } catch (err) {
                alert('Нажмите Ctrl+C для копирования');
            }
            window.getSelection().removeAllRanges();
        });
    </script>
</body>
</html>