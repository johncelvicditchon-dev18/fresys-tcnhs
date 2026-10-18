
function updateVoteCount() {
    fetch('../api/count_votes.php')
        .then(response => response.text())
        .then(data => {
            console.log(data); 
        })
        .catch(err => console.error(err));
}

updateVoteCount();
setInterval(updateVoteCount, 20000);





