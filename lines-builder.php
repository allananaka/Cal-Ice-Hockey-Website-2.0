<?php
session_start();
require __DIR__ . '/../config.php';    

// Check if user is signed in
if (!isset($_SESSION['user'])) {
    header("Location: index.html");
    exit;
}
$isAdmin = ($_SESSION['user']['role'] ?? '') === 'admin';
$canEdit = !empty($_SESSION['user']['can_build_lines']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lines Builder - Coach Dashboard</title>
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon"/>
    <link rel="icon" href="images/favicon.png" type="image/x-icon"/>
    <link rel="stylesheet" href="styles.css?v=1.1">
    <style>
        :root {
            --berkeley-blue: #003262;
            --cal-gold: #FDB515;
            --ink: #0b0b0c;
            --light-gray: #f5f5f5;
            --border-gray: #e0e0e0;
        }

        .linesBuilderPage {
            background-color: #f9f9f9;
            min-height: 100vh;
            padding-top: 150px;
            padding-bottom: 40px;
        }

        .linesBuilderContainer {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .linesBuilderHeader {
            text-align: center;
            margin-bottom: 40px;
        }

        .linesBuilderHeader h1 {
            font-size: 2.5em;
            color: var(--berkeley-blue);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .linesBuilderHeader p {
            color: #666;
            font-size: 1.1em;
        }

        .builderContent {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            align-items: start;
        }

        /* Available Players Sidebar */
        .availablePlayersPanel {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            height: fit-content;
            max-height: calc(100vh - 220px);
            overflow-y: auto;
            sticky: 20px;
            position: sticky;
            top: 150px;
            border: 2px solid var(--border-gray);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .availablePlayersPanel h2 {
            font-size: 1.3em;
            color: var(--berkeley-blue);
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--border-gray);
        }

        .playerCard {
            background: var(--light-gray);
            border: 2px solid var(--border-gray);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
            cursor: grab;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .playerCard:hover {
            background: #efefef;
            border-color: var(--cal-gold);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(253, 181, 21, 0.2);
        }

        .playerCard:active {
            cursor: grabbing;
        }

        .playerCard.dragging {
            opacity: 0.5;
        }

        .playerCardImage {
            width: 45px;
            height: 45px;
            border-radius: 8px;
            object-fit: cover;
            background: #ddd;
        }

        .playerCardInfo {
            flex: 1;
            min-width: 0;
        }

        .playerCardName {
            font-weight: 600;
            color: var(--ink);
            font-size: 0.95em;
            margin-bottom: 4px;
            word-break: break-word;
        }

        .playerCardStats {
            font-size: 0.8em;
            color: #888;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .stat {
            background: white;
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Lines Sheet */
        .linesSheet {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .linesSection {
            margin-bottom: 50px;
        }

        .linesSection h3 {
            font-size: 1.4em;
            color: var(--berkeley-blue);
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--cal-gold);
        }

        .linesGrid {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .line {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
            background: var(--light-gray);
            border-radius: 10px;
            border: 2px solid var(--border-gray);
            min-height: 150px;
            position: relative;
        }

        .line::before {
            content: attr(data-label);
            position: absolute;
            top: -25px;
            left: 0;
            font-size: 0.9em;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
        }

        .line.forwards {
            margin-top: 15px;
        }

        .lineSlot {
            background: white;
            border: 2px dashed var(--border-gray);
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 120px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .lineSlot:hover {
            border-color: var(--cal-gold);
            background: rgba(253, 181, 21, 0.05);
        }

        .lineSlot.dragover {
            border-color: var(--cal-gold);
            background: rgba(253, 181, 21, 0.15);
            transform: scale(1.02);
        }

        .lineSlot.empty {
            color: #aaa;
        }

        .lineSlot.empty::after {
            content: "Drag player here";
            font-size: 0.9em;
            color: #ccc;
            text-align: center;
        }

        .playerInSlot {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 10px;
        }

        .playerInSlotImage {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            background: #ddd;
        }

        .playerInSlotName {
            font-weight: 600;
            color: var(--ink);
            font-size: 0.95em;
        }

        .playerInSlotDetails {
            font-size: 0.8em;
            color: #666;
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .playerInSlotRemove {
            position: absolute;
            top: 5px;
            right: 5px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .lineSlot:hover .playerInSlotRemove {
            opacity: 1;
        }

        .playerInSlotRemove:hover {
            background: #cc0000;
        }

        /* Action Buttons */
        .actionButtons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            justify-content: center;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btnPrimary {
            background: var(--berkeley-blue);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 50, 98, 0.3);
        }

        .btnPrimary:hover {
            background: #001f47;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 50, 98, 0.4);
        }

        .btnSecondary {
            background: var(--cal-gold);
            color: var(--ink);
            border: 2px solid var(--berkeley-blue);
        }

        .btnSecondary:hover {
            background: #f5a600;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .builderContent {
                grid-template-columns: 1fr;
            }

            .availablePlayersPanel {
                position: relative;
                top: 0;
                max-height: none;
            }

            .linesBuilderPage {
                padding-top: 120px;
            }

            .linesBuilderHeader h1 {
                font-size: 1.8em;
            }
        }

        @media (max-width: 768px) {
            .line {
                grid-template-columns: 1fr;
            }

            .linesBuilderHeader h1 {
                font-size: 1.5em;
            }

            .linesSheet {
                padding: 20px;
            }
        }

        /* Scrollbar styling */
        .availablePlayersPanel::-webkit-scrollbar {
            width: 8px;
        }

        .availablePlayersPanel::-webkit-scrollbar-track {
            background: var(--light-gray);
            border-radius: 4px;
        }

        .availablePlayersPanel::-webkit-scrollbar-thumb {
            background: var(--border-gray);
            border-radius: 4px;
        }

        .availablePlayersPanel::-webkit-scrollbar-thumb:hover {
            background: var(--cal-gold);
        }
    </style>
</head>
<body class="linesBuilderPage">
    <header>
        <a href="#" class="logo"><img src="images/calicehockeylogo2.png" alt="California"></a>
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="schedule.html">Schedule</a></li>
            <li><a href="roster.html">Roster</a></li>
            <li><a href="staff.html">Staff</a></li>
            <li><a href="articles.html">Articles</a></li>
            <li class="dropdown">
                <a href="login.php">Team</a>
                <ul class="dropdown-menu">
                    <li><a href="login.php">Login</a></li>
                    <li><a href="https://forms.gle/nP5RbMVFuc2RLnWz9" target="_blank">Recruits</a></li>
                    <li><a href="https://forms.gle/iUhuvuu1Cd8nx1Cd6" target="_blank">Alumni</a></li>
                </ul>
            </li>
        </ul>
    </header>

    <div class="linesBuilderContainer">
        <div class="linesBuilderHeader">
            <h1>Lines Builder</h1>
            <p>Drag players from the left to create your team lines</p>
        </div>

        <div class="builderContent">
            <!-- Available Players Panel -->
            <div class="availablePlayersPanel" id="availablePlayersPanel">
                <h2>Available Players</h2>
                <p style="font-size: 0.85em; color: #999; margin-bottom: 15px;">Drag here to remove from lineup</p>
                <div id="availablePlayers"></div>
            </div>

            <!-- Lines Sheet -->
            <div class="linesSheet">
                <!-- Forwards Lines -->
                <div class="linesSection">
                    <h3>Forwards</h3>
                    <div class="linesGrid" id="forwardsContainer"></div>
                </div>

                <!-- Defense Lines -->
                <div class="linesSection">
                    <h3>Defense</h3>
                    <div class="linesGrid" id="defenseContainer"></div>
                </div>

                <!-- Action Buttons -->
                <div class="actionButtons">
                    <button class="btn btnPrimary" onclick="copyLinesToClipboard()">Copy Lines</button>
                    <button class="btn btnSecondary" onclick="clearAll()">Clear All</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CAN_EDIT = <?= $canEdit ? 'true' : 'false' ?>;
        let players = [];
        let usedPlayerIds = new Set();
        let forwardsLineCount = 1;
        let defenseLineCount = 1;

        // Load players from JSON
        async function loadPlayers() {
            try {
                console.log('Attempting to load roster-data.json...');
                const response = await fetch('roster-data.json');
                console.log('Response status:', response.status);

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('Players loaded successfully:', data);
                // Add id property to each player based on array index (unique identifier)
                players = data.roster.map((player, index) => ({
                    ...player,
                    id: index
                }));
                initializeLineBuilder();
            } catch (error) {
                console.error('Error loading players:', error);
                alert('Failed to load roster-data.json. Make sure you\'re running a local server (see console for details)');
            }
        }

        // Initialize the line builder
        function initializeLineBuilder() {
            renderAvailablePlayers();
            createInitialLines();
        }

        // Render available players
        function renderAvailablePlayers() {
            const container = document.getElementById('availablePlayers');
            container.innerHTML = '';

            const availablePlayers = players.filter(p => !usedPlayerIds.has(p.id));

            if (availablePlayers.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #aaa; padding: 20px;">All players are assigned</p>';
                return;
            }

            availablePlayers.forEach(player => {
                const playerCard = createPlayerCard(player, true);
                container.appendChild(playerCard);
            });
        }

        // Create a player card element
        function createPlayerCard(player, draggable = false) {
            const card = document.createElement('div');
            card.className = 'playerCard';
            card.draggable = draggable;
            card.dataset.playerId = player.id;

            card.innerHTML = `
                <img src="${player.image}" alt="${player.name}" class="playerCardImage" onerror="this.src='images/blank-profile-picture-973460_960_720.jpeg'">
                <div class="playerCardInfo">
                    <div class="playerCardName">#${player.number} ${player.name}</div>
                    <div class="playerCardStats">
                        <span class="stat">${player.position}</span>
                        <span class="stat">${player.hand}</span>
                    </div>
                </div>
            `;

            if (draggable) {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            }

            return card;
        }

        // Create initial lines
        function createInitialLines() {
            createNewForwardLine();
            createNewDefenseLine();
        }

        // Create a new forward line
        function createNewForwardLine() {
            const container = document.getElementById('forwardsContainer');
            const lineNum = forwardsLineCount++;

            const line = document.createElement('div');
            line.className = 'line forwards';
            line.dataset.lineType = 'forwards';
            line.dataset.lineNum = lineNum;
            line.setAttribute('data-label', `Line ${lineNum}`);

            for (let i = 0; i < 3; i++) {
                const slot = createLineSlot('forwards', lineNum, i);
                line.appendChild(slot);
            }

            container.appendChild(line);
        }

        // Create a new defense line
        function createNewDefenseLine() {
            const container = document.getElementById('defenseContainer');
            const lineNum = defenseLineCount++;

            const line = document.createElement('div');
            line.className = 'line defense';
            line.dataset.lineType = 'defense';
            line.dataset.lineNum = lineNum;
            line.setAttribute('data-label', `Defense Line ${lineNum}`);

            for (let i = 0; i < 2; i++) {
                const slot = createLineSlot('defense', lineNum, i);
                line.appendChild(slot);
            }

            container.appendChild(line);
        }

        // Create a line slot
        function createLineSlot(lineType, lineNum, slotIndex) {
            const slot = document.createElement('div');
            slot.className = 'lineSlot empty';
            slot.dataset.lineType = lineType;
            slot.dataset.lineNum = lineNum;
            slot.dataset.slotIndex = slotIndex;

            slot.addEventListener('dragover', handleDragOver);
            slot.addEventListener('drop', handleDrop);
            slot.addEventListener('dragleave', handleDragLeave);

            return slot;
        }

        function makeReadOnlyUI() {
            // disable drags
            document.querySelectorAll('.playerCard').forEach(c => c.draggable = false);
            document.querySelectorAll('.lineSlot').forEach(s => s.classList.add('readonly'));

            // hide remove (×) buttons if any
            const style = document.createElement('style');
            style.textContent = `
                .lineSlot.readonly { pointer-events: none; }
                .playerInSlotRemove { display: none !important; }
                .btnSecondary[disabled] { opacity: .6; cursor: not-allowed; }
            `;
            document.head.appendChild(style);

            // disable “Clear All” (keep Copy Lines enabled)
            const clearBtn = document.querySelector('.btn.btnSecondary');
            if (clearBtn) {
                clearBtn.disabled = true;
                clearBtn.title = 'View-only';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (!CAN_EDIT) makeReadOnlyUI();
        });

        // Drag and drop handlers
        let draggedPlayerData = null;
        


        // Handle drag from player in slot
        function handlePlayerInSlotDragStart(e) {
            if (!CAN_EDIT) return;
            const slot = e.target.closest('.lineSlot');
            const playerId = parseInt(slot.dataset.playerId);

            draggedPlayerData = {
                playerId: playerId,
                fromSlot: true,
                sourceSlot: slot
            };

            e.dataTransfer.effectAllowed = 'move';
            e.target.closest('.playerInSlot').style.opacity = '0.5';
        }

        function handlePlayerInSlotDragEnd(e) {
            if (!CAN_EDIT) return;
            e.target.closest('.playerInSlot').style.opacity = '';
        }

        function handleDragStart(e) {
            if (!CAN_EDIT) return;
            draggedPlayerData = {
                playerId: parseInt(e.target.closest('.playerCard').dataset.playerId),
                fromAvailable: true
            };
            e.target.closest('.playerCard').classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        }

        function handleDragEnd(e) {
            if (!CAN_EDIT) return;
            e.target.closest('.playerCard').classList.remove('dragging');
        }

        function handleDragOver(e) {
            if (!CAN_EDIT) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            e.target.closest('.lineSlot').classList.add('dragover');
        }

        function handleDragLeave(e) {
            if (!CAN_EDIT) return;
            if (e.target.closest('.lineSlot') === e.currentTarget) {
                e.currentTarget.classList.remove('dragover');
            }
        }

        function handleDrop(e) {
            if (!CAN_EDIT) return;
            e.preventDefault();
            e.stopPropagation();

            const slot = e.currentTarget;
            slot.classList.remove('dragover');

            if (!draggedPlayerData) return;

            const player = players.find(p => p.id === draggedPlayerData.playerId);
            if (!player) return;

            const existingPlayerId = slot.dataset.playerId;
            const sourceSlot = draggedPlayerData.sourceSlot;

            // If dragging from another slot to another slot, handle swap/move/duplicate
            if (sourceSlot && sourceSlot !== slot) {
                if (existingPlayerId) {
                    // Swap: move the existing player to the source slot
                    const existingPlayer = players.find(p => p.id === parseInt(existingPlayerId));
                    if (existingPlayer) {
                        populateSlot(sourceSlot, existingPlayer);
                    }
                } else {
                    // Duplicate: keep the source slot and copy to destination (don't remove from source)
                    // This allows the same player to appear in multiple slots
                }
            } else if (existingPlayerId && !sourceSlot) {
                // If dragging from available and target has a player, move existing to available
                usedPlayerIds.delete(parseInt(existingPlayerId));
                removePlayerFromSlot(slot);
            }

            // Add player to slot
            populateSlot(slot, player);

            renderAvailablePlayers();
            checkAndAddNewLines();

            draggedPlayerData = null;
        }

        // Helper function to populate a slot with a player
        function populateSlot(slot, player) {
            if (!CAN_EDIT) return;
            slot.dataset.playerId = player.id;
            slot.classList.remove('empty');
            usedPlayerIds.add(player.id);

            slot.innerHTML = `
                <div class="playerInSlot" draggable="true">
                    <img src="${player.image}" alt="${player.name}" class="playerInSlotImage" onerror="this.src='images/blank-profile-picture-973460_960_720.jpeg'">
                    <div class="playerInSlotName">#${player.number} ${player.name}</div>
                    <div class="playerInSlotDetails">
                        <span>${player.position}</span>
                        <span>${player.hand}</span>
                    </div>
                </div>
                <button class="playerInSlotRemove" onclick="removePlayerFromSlot(this.closest('.lineSlot'))">×</button>
            `;

            // Setup drag handlers for the player in slot
            const playerInSlot = slot.querySelector('.playerInSlot');
            playerInSlot.addEventListener('dragstart', handlePlayerInSlotDragStart);
            playerInSlot.addEventListener('dragend', handlePlayerInSlotDragEnd);
        }

        // Remove player from slot
        function removePlayerFromSlot(slot) {
            const playerId = parseInt(slot.dataset.playerId);
            if (playerId) {
                usedPlayerIds.delete(playerId);
                slot.dataset.playerId = '';
                slot.classList.add('empty');
                slot.innerHTML = '';
                renderAvailablePlayers();
            }
        }

        // Check if new lines need to be added
        function checkAndAddNewLines() {
            checkForwardLines();
            checkDefenseLines();
        }

        function checkForwardLines() {
            const container = document.getElementById('forwardsContainer');
            const lines = container.querySelectorAll('.line');

            if (lines.length > 0) {
                const lastLine = lines[lines.length - 1];
                const slots = lastLine.querySelectorAll('.lineSlot');
                const hasAnyPlayer = Array.from(slots).some(slot => slot.dataset.playerId);

                if (hasAnyPlayer) {
                    createNewForwardLine();
                }
            }
        }

        function checkDefenseLines() {
            const container = document.getElementById('defenseContainer');
            const lines = container.querySelectorAll('.line');

            if (lines.length > 0) {
                const lastLine = lines[lines.length - 1];
                const slots = lastLine.querySelectorAll('.lineSlot');
                const hasAnyPlayer = Array.from(slots).some(slot => slot.dataset.playerId);

                if (hasAnyPlayer) {
                    createNewDefenseLine();
                }
            }
        }

        // Clear all lines
        function clearAll() {
            if (!CAN_EDIT) return;
            if (confirm('Are you sure you want to clear all lines?')) {
                usedPlayerIds.clear();
                const forwardsContainer = document.getElementById('forwardsContainer');
                const defenseContainer = document.getElementById('defenseContainer');
                forwardsContainer.innerHTML = '';
                defenseContainer.innerHTML = '';
                forwardsLineCount = 1;
                defenseLineCount = 1;
                createInitialLines();
                renderAvailablePlayers();
            }
        }

        // Helper function to format player name as initial + last name
        function formatPlayerName(fullName) {
            const parts = fullName.trim().split(' ');
            if (parts.length < 2) {
                return fullName;
            }
            const firstName = parts[0];
            const lastName = parts.slice(1).join(' ');
            return firstName.charAt(0).toUpperCase() + '. ' + lastName;
        }

        // Copy lines to clipboard
        function copyLinesToClipboard() {
            let linesText = '';

            // Add Forwards header
            linesText += 'Forwards\n';

            // Collect forwards
            const forwardsLines = document.querySelectorAll('#forwardsContainer .line');
            if (forwardsLines.length > 0) {
                forwardsLines.forEach((line, lineIndex) => {
                    const slots = line.querySelectorAll('.lineSlot');
                    const playerNames = [];

                    slots.forEach(slot => {
                        if (slot.dataset.playerId) {
                            const player = players.find(p => p.id === parseInt(slot.dataset.playerId));
                            if (player) {
                                playerNames.push(formatPlayerName(player.name));
                            } else {
                                playerNames.push('');
                            }
                        } else {
                            playerNames.push('');
                        }
                    });

                    // Only add line if it has at least one player
                    if (playerNames.some(name => name !== '')) {
                        linesText += playerNames.join(' - ') + '\n';
                    }
                });
            }

            // Add spacing and Defense header
            linesText += '\nDefense\n';

            // Collect defense
            const defenseLines = document.querySelectorAll('#defenseContainer .line');
            if (defenseLines.length > 0) {
                defenseLines.forEach((line, lineIndex) => {
                    const slots = line.querySelectorAll('.lineSlot');
                    const playerNames = [];

                    slots.forEach(slot => {
                        if (slot.dataset.playerId) {
                            const player = players.find(p => p.id === parseInt(slot.dataset.playerId));
                            if (player) {
                                playerNames.push(formatPlayerName(player.name));
                            } else {
                                playerNames.push('');
                            }
                        } else {
                            playerNames.push('');
                        }
                    });

                    // Only add line if it has at least one player
                    if (playerNames.some(name => name !== '')) {
                        linesText += playerNames.join(' - ') + '\n';
                    }
                });
            }

            // Copy to clipboard
            const copyButton = document.querySelector('button[onclick="copyLinesToClipboard()"]');
            if (linesText.trim()) {
                navigator.clipboard.writeText(linesText.trim()).then(() => {
                    const originalColor = copyButton.style.background;
                    copyButton.style.background = '#4CAF50';
                    copyButton.textContent = 'Copied!';
                    setTimeout(() => {
                        copyButton.style.background = originalColor;
                        copyButton.textContent = 'Copy Lines';
                    }, 1500);
                }).catch(err => {
                    console.error('Failed to copy:', err);
                });
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', loadPlayers);

        // Handle header scroll
        window.addEventListener("scroll", function(){
            var header = document.querySelector("header");
            header.classList.toggle("sticky", window.scrollY > 0);
        });
    </script>
</body>
</html>
