const historyPanel = document.getElementById("messageHistory");
    if (historyPanel) historyPanel.scrollTop = historyPanel.scrollHeight;
    const attachmentInput = document.getElementById("messageAttachments");
    const selectedFiles = document.getElementById("selectedMessageFiles");
    const composerForm = document.getElementById("privateMessageForm");
    const recordButton = document.getElementById("recordMessageAudio");
    const recordingStatus = document.getElementById("audioRecordingStatus");
    const composerInput = document.querySelector(".epi-message-composer-input");
    let mediaRecorder = null;
    let recordingStream = null;
    let recordedAudioFile = null;
    let recordingPreviewUrl = null;
    let recordingStartedAt = 0;
    let recordingTimer = null;

    const renderSelectedFiles = () => {
      if (!attachmentInput || !selectedFiles) return;
      const names = Array.from(attachmentInput.files || []).map(file => file.name);
      if (recordedAudioFile) names.push(recordedAudioFile.name);
      selectedFiles.textContent = names.join(", ");
      selectedFiles.hidden = names.length === 0;
    };

    if (attachmentInput && selectedFiles) {
      attachmentInput.addEventListener("change", renderSelectedFiles);
    }

    const releaseRecordingStream = () => {
      if (recordingStream) {
        recordingStream.getTracks().forEach(track => track.stop());
        recordingStream = null;
      }
    };

    const setRecordingStatus = () => {
      if (!recordingStatus || !mediaRecorder || mediaRecorder.state !== "recording") return;
      const elapsedSeconds = Math.floor((Date.now() - recordingStartedAt) / 1000);
      const minutes = String(Math.floor(elapsedSeconds / 60)).padStart(2, "0");
      const seconds = String(elapsedSeconds % 60).padStart(2, "0");
      recordingStatus.textContent = `Enregistrement en cours · ${minutes}:${seconds}`;
    };

    if (recordButton && composerForm && attachmentInput && composerInput && recordingStatus) {
      recordButton.addEventListener("click", async () => {
        if (mediaRecorder && mediaRecorder.state === "recording") {
          mediaRecorder.stop();
          return;
        }
        if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === "undefined") {
          recordingStatus.textContent = "L’enregistrement audio n’est pas pris en charge par ce navigateur.";
          return;
        }

        try {
          recordButton.disabled = true;
          recordingStream = await navigator.mediaDevices.getUserMedia({ audio: true });
          recordButton.disabled = false;
          composerForm.querySelectorAll(".epi-message-recording-preview, .epi-message-recording-remove").forEach(element => element.remove());
          if (recordingPreviewUrl) URL.revokeObjectURL(recordingPreviewUrl);
          recordingPreviewUrl = null;
          recordedAudioFile = null;
          renderSelectedFiles();
          const preferredType = ["audio/webm;codecs=opus", "audio/webm", "audio/mp4"]
            .find(type => MediaRecorder.isTypeSupported(type));
          const recorder = preferredType
            ? new MediaRecorder(recordingStream, { mimeType: preferredType })
            : new MediaRecorder(recordingStream);
          mediaRecorder = recorder;
          const chunks = [];
          recorder.addEventListener("dataavailable", event => {
            if (event.data.size > 0) chunks.push(event.data);
          });
          recorder.addEventListener("stop", () => {
            clearInterval(recordingTimer);
            releaseRecordingStream();
            mediaRecorder = null;
            recordButton.classList.remove("is-recording");
            recordButton.setAttribute("aria-label", "Enregistrer un message vocal");
            recordButton.title = "Enregistrer un message vocal";

            const mimeType = recorder.mimeType || "audio/webm";
            const extension = mimeType.includes("mp4") ? "m4a" : mimeType.includes("ogg") ? "ogg" : "webm";
            const audioBlob = new Blob(chunks, { type: mimeType });
            if (audioBlob.size === 0) {
              recordedAudioFile = null;
              recordingStatus.textContent = "Aucun son n’a été capturé. Vérifiez le microphone et réessayez.";
              renderSelectedFiles();
              return;
            }
            if (audioBlob.size > 10 * 1024 * 1024) {
              recordedAudioFile = null;
              recordingStatus.textContent = "L’audio dépasse la limite de 10 Mo. Enregistrez un message plus court.";
              renderSelectedFiles();
              return;
            }
            recordedAudioFile = new File([audioBlob], `message-vocal-${Date.now()}.${extension}`, { type: mimeType });
            recordingStatus.textContent = "Message vocal prêt à être envoyé.";
            renderSelectedFiles();

            const preview = document.createElement("audio");
            preview.className = "epi-message-recording-preview";
            preview.controls = true;
            recordingPreviewUrl = URL.createObjectURL(audioBlob);
            preview.src = recordingPreviewUrl;
            preview.setAttribute("aria-label", "Écouter le message vocal avant envoi");
            const removeButton = document.createElement("button");
            removeButton.className = "epi-message-recording-remove";
            removeButton.type = "button";
            removeButton.textContent = "Supprimer";
            removeButton.addEventListener("click", () => {
              recordedAudioFile = null;
              preview.pause();
              if (recordingPreviewUrl) URL.revokeObjectURL(recordingPreviewUrl);
              recordingPreviewUrl = null;
              preview.remove();
              removeButton.remove();
              recordingStatus.textContent = "";
              renderSelectedFiles();
            });
            composerInput.insertAdjacentElement("afterend", preview);
            preview.insertAdjacentElement("afterend", removeButton);
          }, { once: true });
          recorder.start();
          recordingStartedAt = Date.now();
          recordButton.classList.add("is-recording");
          recordButton.setAttribute("aria-label", "Arrêter l’enregistrement");
          recordButton.title = "Arrêter l’enregistrement";
          setRecordingStatus();
          recordingTimer = setInterval(setRecordingStatus, 1000);
        } catch (error) {
          recordButton.disabled = false;
          releaseRecordingStream();
          recordingStatus.textContent = error.name === "NotAllowedError"
            ? "Autorisez l’accès au microphone pour enregistrer un audio."
            : "Impossible de démarrer l’enregistrement audio.";
        }
      });

      composerForm.addEventListener("submit", event => {
        if (mediaRecorder && mediaRecorder.state === "recording") {
          event.preventDefault();
          recordingStatus.textContent = "Arrêtez l’enregistrement avant d’envoyer.";
          return;
        }
        if (!recordedAudioFile) return;
        const transfer = new DataTransfer();
        Array.from(attachmentInput.files || []).forEach(file => transfer.items.add(file));
        transfer.items.add(recordedAudioFile);
        attachmentInput.files = transfer.files;
      });
    }

    document.querySelectorAll('[data-confirm-delete="everyone"]').forEach(button => {
      button.addEventListener("click", event => {
        if (!window.confirm("Supprimer définitivement ce message et ses pièces jointes pour vous deux ?")) {
          event.preventDefault();
        }
      });
    });
