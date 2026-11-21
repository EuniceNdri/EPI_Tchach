(() => {
  const rail = document.querySelector('.story-rail');
  const composer = document.getElementById('storyComposerDialog');
  const viewer = document.getElementById('storyViewerDialog');
  if (!rail || !(composer instanceof HTMLDialogElement) || !(viewer instanceof HTMLDialogElement)) return;

  const baseUrl = document.body.dataset.baseUrl || '';
  const csrfToken = rail.dataset.storyCsrf || '';
  const author = document.getElementById('storyViewerAuthor');
  const content = document.getElementById('storyViewerContent');
  const progress = document.getElementById('storyProgress');
  const viewButton = document.getElementById('storyViewersButton');
  const viewersList = document.getElementById('storyViewersList');
  const deleteButton = document.getElementById('storyDeleteButton');
  const groups = Array.from(document.querySelectorAll('[data-story-group]')).map((button) => {
    try {
      return { button, group: JSON.parse(button.dataset.storyGroup || '{}') };
    } catch (error) {
      console.error('Données de story illisibles.', error);
      return null;
    }
  }).filter(Boolean);

  let groupIndex = 0;
  let storyIndex = 0;
  let timer = null;
  let animationFrame = null;
  const duration = 8000;

  function stopProgress() {
    window.clearTimeout(timer);
    window.cancelAnimationFrame(animationFrame);
    timer = null;
    animationFrame = null;
  }

  function startProgress() {
    stopProgress();
    const started = performance.now();
    const tick = (now) => {
      const percent = Math.min(100, ((now - started) / duration) * 100);
      progress.style.setProperty('--story-progress', `${percent}%`);
      if (percent < 100) animationFrame = window.requestAnimationFrame(tick);
    };
    animationFrame = window.requestAnimationFrame(tick);
    timer = window.setTimeout(nextStory, duration);
  }

  async function sendForm(path, values) {
    const body = new FormData();
    Object.entries({ ...values, csrf_token: csrfToken }).forEach(([key, value]) => body.append(key, value));
    const response = await fetch(`${baseUrl}/${path}`, {
      method: 'POST',
      body,
      headers: { Accept: 'application/json' },
      credentials: 'same-origin',
    });
    const result = await response.json();
    if (!response.ok || !result.success) throw new Error(result.message || 'Une erreur est survenue.');
    return result;
  }

  function currentStory() {
    return groups[groupIndex]?.group.stories?.[storyIndex] || null;
  }

  async function showStory() {
    const entry = groups[groupIndex];
    const story = currentStory();
    if (!entry || !story) return closeViewer();

    author.textContent = entry.group.name || 'Utilisateur';
    content.replaceChildren();
    viewersList.hidden = true;
    viewersList.replaceChildren();
    viewButton.hidden = !entry.group.is_owner;
    viewButton.textContent = `${story.views} vue${story.views === 1 ? '' : 's'} · voir les personnes`;
    deleteButton.hidden = !entry.group.is_owner;
    progress.style.setProperty('--story-progress', '0%');

    if (story.image_url) {
      const image = new Image();
      image.src = `${baseUrl}/stories/image?id=${encodeURIComponent(story.id)}`;
      image.alt = 'Image de la story';
      content.append(image);
    }
    if (story.text) {
      const text = document.createElement('div');
      text.className = 'story-text';
      text.textContent = story.text;
      content.append(text);
    }

    if (!entry.group.is_owner) {
      try {
        await sendForm('stories/view', { story_id: String(story.id) });
      } catch (error) {
        console.error('La story n’a pas pu être marquée comme vue.', error);
      }
    }
    startProgress();
  }

  function closeViewer() {
    stopProgress();
    if (viewer.open) viewer.close();
  }

  function nextStory() {
    stopProgress();
    if (storyIndex + 1 < groups[groupIndex].group.stories.length) {
      storyIndex += 1;
    } else if (groupIndex + 1 < groups.length) {
      groupIndex += 1;
      storyIndex = 0;
    } else {
      closeViewer();
      return;
    }
    showStory();
  }

  function previousStory() {
    stopProgress();
    if (storyIndex > 0) {
      storyIndex -= 1;
    } else if (groupIndex > 0) {
      groupIndex -= 1;
      storyIndex = groups[groupIndex].group.stories.length - 1;
    } else {
      storyIndex = 0;
    }
    showStory();
  }

  document.getElementById('openStoryComposer')?.addEventListener('click', () => composer.showModal());
  document.querySelectorAll('[data-close-story-dialog]').forEach((button) => {
    button.addEventListener('click', () => {
      composer.close();
      closeViewer();
    });
  });
  groups.forEach(({ button }, index) => {
    button.addEventListener('click', () => {
      groupIndex = index;
      storyIndex = 0;
      viewer.showModal();
      showStory();
    });
  });
  document.getElementById('storyNext')?.addEventListener('click', nextStory);
  document.getElementById('storyPrevious')?.addEventListener('click', previousStory);
  viewButton.addEventListener('click', async () => {
    const story = currentStory();
    if (!story) return;
    viewButton.disabled = true;
    try {
      const response = await fetch(`${baseUrl}/stories/viewers?id=${encodeURIComponent(story.id)}`, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || 'Impossible de charger les vues.');
      viewersList.replaceChildren();
      if (!result.viewers.length) {
        viewersList.textContent = 'Aucune vue pour le moment.';
      } else {
        result.viewers.forEach((person) => {
          const row = document.createElement('div');
          row.textContent = `${person.prenom} ${person.nom}`;
          viewersList.append(row);
        });
      }
      viewersList.hidden = false;
    } catch (error) {
      viewersList.textContent = error.message;
      viewersList.hidden = false;
    } finally {
      viewButton.disabled = false;
    }
  });
  deleteButton.addEventListener('click', async () => {
    const story = currentStory();
    if (!story || !window.confirm('Supprimer cette story ?')) return;
    deleteButton.disabled = true;
    try {
      await sendForm('stories/delete', { story_id: String(story.id) });
      window.location.reload();
    } catch (error) {
      window.alert(error.message);
      deleteButton.disabled = false;
    }
  });
  viewer.addEventListener('click', (event) => {
    if (event.target === viewer) closeViewer();
  });
  viewer.addEventListener('close', stopProgress);
  document.addEventListener('keydown', (event) => {
    if (!viewer.open) return;
    if (event.key === 'ArrowRight') nextStory();
    if (event.key === 'ArrowLeft') previousStory();
    if (event.key === 'Escape') closeViewer();
  });
  document.getElementById('storyImage')?.addEventListener('change', (event) => {
    const file = event.target.files?.[0];
    if (file && file.size > 10 * 1024 * 1024) {
      event.target.value = '';
      window.alert('L’image ne doit pas dépasser 10 Mo.');
    }
  });
})();
