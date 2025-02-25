<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'prestataire') {
    header("Location: login.php");
    exit;
}
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Espace Prestataire | DJ Prestige Sound</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="espace-prestataire.css">
  <style>
    /* Loader CSS */
    #loader {
      border: 8px solid #f3f3f3;
      border-top: 8px solid #FF8C42;
      border-radius: 50%;
      width: 60px;
      height: 60px;
      animation: spin 1s linear infinite;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 9999;
    }
    @keyframes spin {
      0% { transform: translate(-50%, -50%) rotate(0deg); }
      100% { transform: translate(-50%, -50%) rotate(360deg); }
    }
    /* Barre de recherche */
    #search-bar {
      margin: 1rem auto;
      max-width: 400px;
      display: flex;
      justify-content: center;
    }
    #search-bar input {
      padding: 0.5rem 1rem;
      border: 1px solid #FF8C42;
      border-radius: 50px 0 0 50px;
      outline: none;
      width: 70%;
    }
    #search-bar button {
      padding: 0.5rem 1rem;
      border: none;
      background: #FF8C42;
      color: #111;
      border-radius: 0 50px 50px 0;
      cursor: pointer;
      transition: background 0.3s;
    }
    #search-bar button:hover {
      background: #FFA564;
    }
    /* Bouton de rafraîchissement */
    #refresh-btn {
      margin: 1rem auto;
      display: block;
      padding: 0.5rem 1rem;
      background: #FFA564;
      color: #111;
      font-weight: bold;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      transition: background 0.3s;
    }
    #refresh-btn:hover {
      background: #FF8C42;
    }
  </style>
</head>
<body>
  <!-- Loader -->
  <div id="loader"></div>

  <!-- HEADER -->
  <header class="site-header">
    <div class="container">
      <h1 class="prestataire-title">🎤 Espace Prestataire</h1>
    </div>
  </header>

  <!-- Barre de recherche & rafraîchissement -->
  <section id="controls">
    <div id="search-bar">
      <input type="text" id="search-input" placeholder="Rechercher un événement assigné...">
      <button onclick="applyFilter()">🔍</button>
    </div>
    <button id="refresh-btn" onclick="loadEvents()">↻ Rafraîchir la liste</button>
  </section>

  <!-- LISTE DES ÉVÉNEMENTS ASSIGNÉS -->
  <section class="event-section">
    <div class="container">
      <div id="event-list">
        <p>Chargement des événements...</p>
      </div>
    </div>
  </section>

  <!-- JAVASCRIPT -->
  <script>
    window.addEventListener('load', () => {
      document.getElementById('loader').style.display = 'none';
      loadEvents();
    });

    function loadEvents() {
      document.getElementById('loader').style.display = 'block';
      fetch("fetch_data.php?type=prestataires")
        .then(response => response.json())
        .then(data => {
          document.getElementById('loader').style.display = 'none';
          renderEvents(data);
        })
        .catch(error => {
          console.error("Erreur lors du chargement:", error);
          document.getElementById('loader').style.display = 'none';
          document.getElementById("event-list").innerHTML = "<p>Impossible de charger les événements.</p>";
        });
    }

    function renderEvents(data) {
      const eventList = document.getElementById("event-list");
      eventList.innerHTML = "";
      if (!data || data.length < 1) {
        eventList.innerHTML = "<p>Aucun événement assigné.</p>";
        return;
      }
      data.forEach(event => {
        let eventCard = document.createElement("div");
        eventCard.className = "event-card";
        eventCard.style.opacity = 0;
        eventCard.innerHTML = `
          <h2>🎶 ${event["Nom du Prestataire"] || "Prestataire inconnu"}</h2>
          <p><strong>Rôle :</strong> ${event["Rôle"] || "Non défini"}</p>
          <p><strong>Événement :</strong> ${event["Événements Assignés"] || "Non défini"}</p>
          <p><strong>Téléphone :</strong> ${event["Téléphone"] || "Non défini"}</p>
          <p><strong>Lieu :</strong>
            <a href="https://www.google.com/maps?q=${event["Lieu"]}" target="_blank">
              ${event["Lieu"] || "Non défini"} 📍
            </a>
          </p>
        `;
        eventList.appendChild(eventCard);
        setTimeout(() => {
          eventCard.style.transition = "opacity 0.5s ease-in-out";
          eventCard.style.opacity = 1;
        }, 100);
      });
    }

    function applyFilter() {
      const query = document.getElementById('search-input').value.toLowerCase();
      const eventCards = document.querySelectorAll(".event-card");
      eventCards.forEach(card => {
        const title = card.querySelector("h2")?.textContent.toLowerCase() || "";
        card.style.display = title.includes(query) ? "" : "none";
      });
    }
  </script>
</body>
</html>
