
// ELEMENTS

const collectableList = document.getElementById("collectable-list");
const challengeList = document.getElementById("challenge-list");

const collectableTemplate = document.getElementById(
    "collectable-template"
);

const challengeTemplate = document.getElementById(
    "challenge-template"
);

const addCollectableButton = document.getElementById(
    "add-collectable"
);

const addChallengeButton = document.getElementById(
    "add-challenge"
);

const mapForm = document.getElementById("map-form");


// INDEXES

let collectableIndex = 0;
let challengeIndex = 0;


// ADD COLLECTABLE

function addCollectable() {

    const html = collectableTemplate.innerHTML.replaceAll(
        "__INDEX__",
        collectableIndex
    );

    collectableList.insertAdjacentHTML("beforeend", html);

    collectableIndex++;
}


// ADD CHALLENGE

function addChallenge() {

    const html = challengeTemplate.innerHTML.replaceAll(
        "__INDEX__",
        challengeIndex
    );

    challengeList.insertAdjacentHTML("beforeend", html);

    // First challenge is the main challenge by default
    if (challengeIndex === 0) {

        challengeList.querySelector(
            'input[name="main_challenge"]'
        ).checked = true;

    }

    challengeIndex++;
}


// REMOVE ITEMS

collectableList.addEventListener("click", (event) => {

    const button = event.target.closest(".remove-item-button");

    if (!button) return;

    button.closest(".collectable-card").remove();

});


challengeList.addEventListener("click", (event) => {

    const button = event.target.closest(".remove-item-button");

    if (!button) return;

    const card = button.closest(".challenge-card");

    const wasMain = card.querySelector(
        'input[name="main_challenge"]'
    ).checked;

    card.remove();


    if (wasMain) {

        const firstChallenge = challengeList.querySelector(
            'input[name="main_challenge"]'
        );

        if (firstChallenge) {
            firstChallenge.checked = true;
        }

    }

});


// BUTTON EVENTS

addCollectableButton.addEventListener("click", addCollectable);

addChallengeButton.addEventListener("click", addChallenge);


 
// FORM VALIDATION
 

mapForm.addEventListener("submit", (event) => {

    const collectables = collectableList.querySelectorAll(
        ".collectable-card"
    );

    const challenges = challengeList.querySelectorAll(
        ".challenge-card"
    );

    // At least one main challenge is required
    const mainChallenge = challengeList.querySelector(
        'input[name="main_challenge"]:checked'
    );

    if (challenges.length > 0 && !mainChallenge) {

        event.preventDefault();

        alert("Please select a main challenge.");

        return;
    }


    // Validate collectable amounts

    for (const card of collectables) {

        const inputs = card.querySelectorAll(
            'input[type="number"]'
        );

        const current = Number(inputs[0].value);
        const maximum = Number(inputs[1].value);

        if (current > maximum) {

            event.preventDefault();

            alert(
                "A collectable's current amount cannot exceed its maximum."
            );

            return;
        }

    }

});


 
// INITIAL ITEMS
 

addCollectable();
addChallenge();
