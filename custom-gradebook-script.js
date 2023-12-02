// custom-script.js
(function ($) {
  // Your JavaScript code here
  window.changeContent = function (answer_info, question_info, model_answer) {
    let { answer_data } = answer_info;
    let { title, question } = question_info;
    let modalTitle = document.querySelector(".modal-title");
    let modalBody = document.querySelector(".modal-body");
    modalBody.innerHTML = "";
    modalTitle.innerHTML = question;
    console.log(answer_info);
    console.log(question_info);
    console.log(model_answer);
    let ul = document.createElement("ul");
    ul.classList.add("ul-container");
    let keys = Object.keys(model_answer);
    let parsed_answers = JSON.parse(answer_data);
    for (let i = 0; i < keys.length; i++) {
      let key = keys[i];
      let value = model_answer[key];

      let li = document.createElement("li");
      li.classList.add("answer_li");

      // Set the text content to key: value
      li.textContent = `${key}`;
      if (value == 1) {
        li.classList.add("correct_answer");
      } else {
        li.classList.add("wrong_answer");
      }

      console.log(parsed_answers[i]);
      if (parsed_answers[i] == "1") {
        li.classList.add("selected_answer");
      }
      // Append the list item to the unordered list
      ul.appendChild(li);
    }
    modalBody.appendChild(ul);
  };

  // Other JavaScript code
})(jQuery);
