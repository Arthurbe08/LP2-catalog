
// ELEMENTS

const mapList = document.getElementById("user-map-list");
const mapSearch = document.getElementById("map-search");


// RENDER MAPS

function renderMaps(mapArray) {

    mapList.replaceChildren();

    // No maps found

    if (mapArray.length === 0) {

        const message = document.createElement("p");

        message.classList.add("empty-message");

        message.textContent = maps.length === 0
            ? "You haven't registered any maps yet."
            : "No maps found with that name.";

        mapList.appendChild(message);

        return;
    }


    // Create map cards

    mapArray.forEach(map => {

        const card = document.createElement("article");

        card.classList.add("map-card");

        card.setAttribute("role", "link");
        card.setAttribute("tabindex", "0");
        card.setAttribute("aria-label", "Open map: " + map.name);

        function openMap() {
            window.location.href =
                "map.php?id=" + encodeURIComponent(map.id_map) + "&from=user";
        }

        card.addEventListener("click", openMap);

        card.addEventListener("keydown", (event) => {

            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                openMap();
            }

        });


        // Username

        const username = document.createElement("p");

        username.classList.add("map-username");

        username.textContent = "by " + map.username;


        // Map name

        const title = document.createElement("h3");

        title.textContent = map.name;


        // Description

        const description = document.createElement("p");

        description.classList.add("map-description");

        description.textContent = map.description ?? "";


        // Map details

        const details = document.createElement("div");

        details.classList.add("map-details");


        const author = document.createElement("span");

        author.textContent = "Author: " + (map.author ?? "Unknown");


        const rating = document.createElement("span");

        rating.textContent = map.rating !== null
            ? "Personal rating: " + map.rating
            : "Personal rating: Not rated";


        const duration = document.createElement("span");

        duration.textContent = map.duration !== null
            ? "Duration: " + map.duration + " min"
            : "Duration: Not specified";


        details.append(author, rating, duration);


        // Collectables

        const collectables = document.createElement("div");

        collectables.classList.add("map-collectables");


        (map.collectables || []).forEach(item => {

            const collectable = document.createElement("div");

            collectable.classList.add("collectable");


            // Collectable image

            if (item.image) {

                const image = document.createElement("img");

                image.src = item.image;

                image.alt = item.name;

                collectable.appendChild(image);

            }


            // Collectable amount

            const amount = document.createElement("span");

            amount.textContent =
                item.current_amount + "/" + item.max_amount;


            collectable.appendChild(amount);

            collectables.appendChild(collectable);

        });


        // Assemble card

        card.append(
            username,
            title,
            description,
            details,
            collectables
        );

        mapList.appendChild(card);

    });

}


// MAP SEARCH

mapSearch.addEventListener("input", () => {

    const search = mapSearch.value.trim().toLowerCase();

    const filteredMaps = maps.filter(map =>
        (map.name ?? "").toLowerCase().includes(search)
    );

    renderMaps(filteredMaps);

});


// INITIAL RENDER

renderMaps(maps);
