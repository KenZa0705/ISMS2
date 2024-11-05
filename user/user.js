
    document.addEventListener("DOMContentLoaded", function () {
        const cardBodies = document.querySelectorAll(".card-body p:not(.card-text)");
        cardBodies.forEach(body => {
            const fullText = body.textContent.trim();
            const words = fullText.split(" ");

            if (words.length > 15) {
                const truncatedText = words.slice(0, 15).join(" ") + "... ";
                const seeMoreLink = document.createElement("a");
                seeMoreLink.href = "#";
                seeMoreLink.textContent = "See more";
                seeMoreLink.style.textDecoration = "none";
                seeMoreLink.style.color = "black"

                body.textContent = truncatedText;
                body.appendChild(seeMoreLink);

                seeMoreLink.addEventListener("click", function (event) {
                    event.preventDefault();
                    body.textContent = fullText;
                });
            }
        });
    });


