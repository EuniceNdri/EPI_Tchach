document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("salonPostForm");
  if (!form) return;

  const baseUrl = document.body.dataset.baseUrl || "";
  const categorySelect = form.querySelector("#modalCategorySelect");
  const hiddenInputs = form.querySelector("[data-composer-metadata-inputs]");
  const tagPicker = form.querySelector('[data-composer-picker="tags"]');
  const mentionPicker = form.querySelector('[data-composer-picker="mentions"]');
  if (!categorySelect || !hiddenInputs || !tagPicker || !mentionPicker) return;

  const tagInput = tagPicker.querySelector("input");
  const mentionInput = mentionPicker.querySelector("input");
  const tagSuggestions = tagPicker.querySelector("[role='listbox']");
  const mentionSuggestions = mentionPicker.querySelector("[role='listbox']");
  const tagStatus = tagPicker.querySelector("[data-tag-status]");
  const mentionStatus = mentionPicker.querySelector("[data-mention-status]");
  const selectedTagsContainer = tagPicker.querySelector("[data-selected-tags]");
  const selectedMentionsContainer = mentionPicker.querySelector("[data-selected-mentions]");
  const selectedTags = new Map();
  const selectedMentions = new Map();
  let tagTimer;
  let mentionTimer;
  let tagRequest;
  let mentionRequest;

  const normalized = (value) => String(value).trim().replace(/^#/, "").toLowerCase();
  const makeChip = (label, key, kind) => {
    const chip = document.createElement("span");
    chip.className = "epi-composer-chip";
    chip.append(document.createTextNode(label));

    const remove = document.createElement("button");
    remove.type = "button";
    remove.textContent = "×";
    remove.setAttribute("aria-label", `Retirer ${label}`);
    remove.dataset.removeKind = kind;
    remove.dataset.removeKey = String(key);
    chip.append(remove);
    return chip;
  };

  const renderHiddenInputs = () => {
    hiddenInputs.replaceChildren();
    selectedTags.forEach((tag) => {
      if (tag.id) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = "tag_ids[]";
        input.value = String(tag.id);
        hiddenInputs.append(input);
      }
    });

    const proposals = [...selectedTags.values()]
      .filter((tag) => !tag.id)
      .map((tag) => tag.name);
    if (proposals.length) {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "tag_proposals";
      input.value = proposals.join(",");
      hiddenInputs.append(input);
    }

    selectedMentions.forEach((person) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "mentioned_users[]";
      input.value = String(person.id);
      hiddenInputs.append(input);
    });
  };

  const renderSelected = () => {
    selectedTagsContainer.replaceChildren();
    selectedTags.forEach((tag, key) => {
      selectedTagsContainer.append(makeChip(`#${tag.name}`, key, "tag"));
    });
    selectedMentionsContainer.replaceChildren();
    selectedMentions.forEach((person, key) => {
      selectedMentionsContainer.append(makeChip(person.name, key, "mention"));
    });
    renderHiddenInputs();
  };

  const showEmpty = (container, text) => {
    container.replaceChildren();
    const message = document.createElement("div");
    message.className = "epi-composer-suggestion";
    message.textContent = text;
    container.append(message);
  };

  const searchTags = async () => {
    tagRequest?.abort();
    tagRequest = new AbortController();
    const query = tagInput.value.trim().replace(/^#/, "");
    tagStatus.textContent = "";
    tagSuggestions.hidden = false;
    showEmpty(tagSuggestions, "Recherche des tags…");

    try {
      const url = new URL(`${baseUrl}/api/tags/search`, window.location.origin);
      if (query) url.searchParams.set("q", query);
      const response = await fetch(url, {
        headers: { Accept: "application/json" },
        signal: tagRequest.signal,
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || "Les tags n’ont pas pu être chargés.");
      }

      tagSuggestions.replaceChildren();
      const tags = Array.isArray(result.tags) ? result.tags : [];
      tags.forEach((tag) => {
        const name = String(tag.nom || "");
        const key = `id:${Number(tag.id)}`;
        if (!name || selectedTags.has(key)) return;
        const option = document.createElement("button");
        option.type = "button";
        option.className = "epi-composer-suggestion";
        option.setAttribute("role", "option");
        option.textContent = `#${name}`;
        option.dataset.tagId = String(tag.id);
        option.dataset.tagName = name;
        tagSuggestions.append(option);
      });

      const proposalName = query.trim();
      const proposalKey = `proposal:${normalized(proposalName)}`;
      const proposalValid = /^[\p{L}\p{N}][\p{L}\p{N} _-]*$/u.test(proposalName)
        && proposalName.length <= 50;
      const existingTag = tags.some((tag) => normalized(tag.nom) === normalized(proposalName));
      if (query && proposalValid && !existingTag && !selectedTags.has(proposalKey)) {
        const proposedCount = [...selectedTags.values()].filter((tag) => !tag.id).length;
        const proposal = document.createElement("button");
        proposal.type = "button";
        proposal.className = "epi-composer-suggestion";
        proposal.setAttribute("role", "option");
        proposal.textContent = proposedCount < 5
          ? `Proposer le tag « ${proposalName} »`
          : "Limite de 5 propositions atteinte";
        proposal.disabled = proposedCount >= 5;
        if (!proposal.disabled) {
          proposal.dataset.proposedTag = proposalName;
          tagSuggestions.append(proposal);
        } else {
          proposal.setAttribute("aria-disabled", "true");
          tagSuggestions.append(proposal);
        }
      }

      if (!tagSuggestions.children.length) {
        showEmpty(tagSuggestions, query ? "Aucun tag correspondant." : "Aucun tag disponible.");
      }
    } catch (error) {
      if (error.name === "AbortError") return;
      console.error("Recherche des tags impossible :", error);
      tagStatus.textContent = error.message || "Les tags n’ont pas pu être chargés.";
      tagSuggestions.hidden = true;
    }
  };

  const searchMentions = async () => {
    mentionRequest?.abort();
    mentionRequest = new AbortController();
    if (!categorySelect.value) {
      mentionStatus.textContent = "Choisissez d’abord une catégorie.";
      mentionSuggestions.hidden = true;
      return;
    }

    mentionStatus.textContent = "";
    mentionSuggestions.hidden = false;
    showEmpty(mentionSuggestions, "Recherche des personnes…");

    try {
      const url = new URL(`${baseUrl}/api/mentions/search`, window.location.origin);
      url.searchParams.set("category_id", categorySelect.value);
      if (mentionInput.value.trim()) url.searchParams.set("q", mentionInput.value.trim());
      const response = await fetch(url, {
        headers: { Accept: "application/json" },
        signal: mentionRequest.signal,
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error(result.message || "Les personnes n’ont pas pu être chargées.");
      }

      mentionSuggestions.replaceChildren();
      const people = Array.isArray(result.users) ? result.users : [];
      people.forEach((person) => {
        const id = Number(person.id);
        if (!id || selectedMentions.has(String(id))) return;
        const option = document.createElement("button");
        option.type = "button";
        option.className = "epi-composer-suggestion";
        option.setAttribute("role", "option");
        option.dataset.personId = String(id);
        option.dataset.personName = `${person.prenom || ""} ${person.nom || ""}`.trim();
        const name = document.createElement("span");
        name.textContent = option.dataset.personName;
        const group = document.createElement("small");
        group.textContent = String(person.role_group || person.role || "Membre");
        option.append(name, group);
        mentionSuggestions.append(option);
      });

      if (!mentionSuggestions.children.length) {
        showEmpty(mentionSuggestions, "Aucune personne trouvée.");
      }
    } catch (error) {
      if (error.name === "AbortError") return;
      console.error("Recherche des personnes impossible :", error);
      mentionStatus.textContent = error.message || "Les personnes n’ont pas pu être chargées.";
      mentionSuggestions.hidden = true;
    }
  };

  const schedule = (callback, timerName, delay = 220) => {
    window.clearTimeout(timerName === "tags" ? tagTimer : mentionTimer);
    const timer = window.setTimeout(callback, delay);
    if (timerName === "tags") tagTimer = timer;
    else mentionTimer = timer;
  };

  tagInput.addEventListener("focus", () => searchTags());
  tagInput.addEventListener("input", () => schedule(searchTags, "tags"));
  mentionInput.addEventListener("focus", () => searchMentions());
  mentionInput.addEventListener("input", () => schedule(searchMentions, "mentions"));

  categorySelect.addEventListener("change", () => {
    mentionRequest?.abort();
    mentionSuggestions.hidden = true;
    mentionSuggestions.replaceChildren();
    if (selectedMentions.size) {
      selectedMentions.clear();
      renderSelected();
      mentionStatus.textContent = "Les personnes associées ont été effacées après le changement de catégorie.";
    }
  });

  tagSuggestions.addEventListener("click", (event) => {
    const option = event.target.closest("button");
    if (!option || option.disabled) return;
    if (option.dataset.tagId) {
      const id = Number(option.dataset.tagId);
      const key = `id:${id}`;
      if (selectedTags.size >= 10) {
        tagStatus.textContent = "Vous pouvez associer au maximum 10 tags.";
        return;
      }
      selectedTags.set(key, { id, name: option.dataset.tagName });
    } else if (option.dataset.proposedTag) {
      if (selectedTags.size >= 10) {
        tagStatus.textContent = "Vous pouvez associer au maximum 10 tags.";
        return;
      }
      const name = option.dataset.proposedTag;
      selectedTags.set(`proposal:${normalized(name)}`, { name });
    } else {
      return;
    }
    tagInput.value = "";
    tagStatus.textContent = "";
    tagSuggestions.hidden = true;
    renderSelected();
  });

  mentionSuggestions.addEventListener("click", (event) => {
    const option = event.target.closest("button[data-person-id]");
    if (!option) return;
    if (selectedMentions.size >= 10) {
      mentionStatus.textContent = "Vous pouvez associer au maximum 10 personnes.";
      return;
    }
    selectedMentions.set(option.dataset.personId, {
      id: Number(option.dataset.personId),
      name: option.dataset.personName,
    });
    mentionInput.value = "";
    mentionStatus.textContent = "";
    mentionSuggestions.hidden = true;
    renderSelected();
  });

  form.addEventListener("click", (event) => {
    const removeButton = event.target.closest("button[data-remove-kind]");
    if (removeButton) {
      if (removeButton.dataset.removeKind === "tag") {
        selectedTags.delete(removeButton.dataset.removeKey);
      } else {
        selectedMentions.delete(removeButton.dataset.removeKey);
      }
      renderSelected();
      return;
    }
    if (!tagPicker.contains(event.target)) tagSuggestions.hidden = true;
    if (!mentionPicker.contains(event.target)) mentionSuggestions.hidden = true;
  });

  form.addEventListener("reset", () => {
    window.setTimeout(() => {
      selectedTags.clear();
      selectedMentions.clear();
      tagStatus.textContent = "";
      mentionStatus.textContent = "";
      tagSuggestions.hidden = true;
      mentionSuggestions.hidden = true;
      renderSelected();
    });
  });
});
