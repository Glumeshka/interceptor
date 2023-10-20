// кастылим айди текущего пользователя
const user_id = $('#user_data').val();

// выпадающее меню справа сверху
$(document).ready(function(){
  $('#action_menu_btn').click(function(){
    $('.action_menu').toggle();
  });
});

// модальные окна
$('#create_user').on('click', function() {
  $('.create_user__popup').addClass('active');
});

$('#ban_user').on('click', function() {
  $('.ban_user__popup').addClass('active');
});

$('#stats_sys').on('click', function() {
  $('.stats_sys__popup').addClass('active');
});

$('#offers_web').on('click', function() {
  $('.offers_web__popup').addClass('active');
});

$('#stats_web').on('click', function() {
  $('.stats_web__popup').addClass('active');
});

$('#offers_add').on('click', function() {
  $('.offers_add__popup').addClass('active');
});

$('#offers_adv').on('click', function() {
  $('.offers_adv__popup').addClass('active');
});

$('#stats_adv').on('click', function() {
  $('.stats_adv__popup').addClass('active');
});

$('#exit').on('click', function() {
  window.location.href = '/Board/logout';
});

// крестик для закрытия модальных окон
$('.close_popup').on('click', function() {
  $('.create_user__popup').removeClass('active');
  $('.ban_user__popup').removeClass('active');  
  $('.stats_sys__popup').removeClass('active');  
  $('.offers_web__popup').removeClass('active');
  $('.stats_web__popup').removeClass('active');
  $('.offers_add__popup').removeClass('active');
  $('.offers_adv__popup').removeClass('active');
  $('.stats_adv__popup').removeClass('active');
});
  
// регистрация пользователя
$(document).ready(function(){
  $('#createUser').click(function(event){
    event.preventDefault();
    let userValue = $('#userEmail').val();
    let passwordValue = $('#userPassword').val();
    let password2Value = $('#userPassword2').val();
    let roleValue = $('input[name="role"]:checked').val();
    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (userValue == '' || passwordValue == '' || password2Value == '') {

      $('#msg_info').text('Пустые поля недопустимы');
      setTimeout(function() {        
        $('#msg_info').text('');
      }, 3000);

    } else if (emailRegex.test(userValue)) {

      $.ajax({
        method: "POST",
        url: "Board/createUser",
        data: { email: userValue,
                password: passwordValue,
                password2: password2Value,
                role: roleValue }
      })

      .done(function(msg) {      
        let data = JSON.parse(msg);
        let message = data.message;
        let active = data.active;
        let inactive = data.inactive;
        
        $('#msg_info').text(message);
        setTimeout(function() {          
          $('#msg_info').text('');
        }, 3000);

        if(message == 'Пользователь успешно создан') {
          $('#noban_user_list').empty();
          $('#ban_user_list').empty();

          active.forEach(function (user) {
            let option = document.createElement('option');
            option.setAttribute('data-user', user.id);
            option.innerText = user.email + ' | ' + user.role;
            $('#noban_user_list').append(option);
          });

          inactive.forEach(function (user) {
            let option = document.createElement('option');
            option.setAttribute('data-user', user.id);
            option.innerText = user.email + ' | ' + user.role;
            $('#ban_user_list').append(option);
          });

        }

        $('#userEmail').val('');
        $('#userPassword').val('');
        $('#userPassword2').val(''); 
      });

    } else {

      $('#msg_info').text('Введите почту корректно');
      setTimeout(function() {        
        $('#msg_info').text('');
      }, 3000);
    }  
  });
});

//бан пользователей 
$(document).ready(function(){
  $('#button_ban').click(function(){

    let selectedUser = $('#noban_user_list').find('option:selected').data('user');  

    if (typeof selectedUser !== 'undefined' && selectedUser !== null) {

      $.ajax({
        method: "POST",
        url: "Board/banUser",
        data: { 
          id_user: selectedUser
        }
      })

      .done(function(msg) {   
        let data = JSON.parse(msg);
        let message = data.message;
        let active = data.active;
        let inactive = data.inactive;

        $('#ban_info').text(message);
        setTimeout(function() {          
          $('#ban_info').text('');
        }, 3000);

        $('#noban_user_list').empty();
        $('#ban_user_list').empty();

        active.forEach(function (user) {
          let option = document.createElement('option');
          option.setAttribute('data-user', user.id);
          option.innerText = user.email + ' | ' + user.role;
          $('#noban_user_list').append(option);
        });

        inactive.forEach(function (user) {
          let option = document.createElement('option');
          option.setAttribute('data-user', user.id);
          option.innerText = user.email + ' | ' + user.role;
          $('#ban_user_list').append(option);
        });
      });

    } else {
   
      $('#ban_info').text('Выбирете пользователя');
      setTimeout(function() {          
        $('#ban_info').text('');
      }, 3000);

    }
  });
});

//разбан пользователей
$(document).ready(function(){
  $('#button_unban').click(function(){

    let selectedUser = $('#ban_user_list').find('option:selected').data('user');  

    if (typeof selectedUser !== 'undefined' && selectedUser !== null) {

      $.ajax({
        method: "POST",
        url: "Board/unbanUser",
        data: { 
          id_user: selectedUser
        }
      })

      .done(function(msg) {   
        let data = JSON.parse(msg);
        let message = data.message;
        let active = data.active;
        let inactive = data.inactive;

        $('#ban_info').text(message);
        setTimeout(function() {          
          $('#ban_info').text('');
        }, 3000);

        $('#noban_user_list').empty();
        $('#ban_user_list').empty();

        active.forEach(function (user) {
          let option = document.createElement('option');
          option.setAttribute('data-user', user.id);
          option.innerText = user.email + ' | ' + user.role;
          $('#noban_user_list').append(option);
        });

        inactive.forEach(function (user) {
          let option = document.createElement('option');
          option.setAttribute('data-user', user.id);
          option.innerText = user.email + ' | ' + user.role;
          $('#ban_user_list').append(option);
        });
      });

    } else {
   
      $('#ban_info').text('Выбирете пользователя');
      setTimeout(function() {          
        $('#ban_info').text('');
      }, 3000);

    }
  });
});

//получение статистики системы
$(document).ready(function(){
  $('#statsSys').click(function(){
    let oneData = $('#date_start').val();
    let twoData = $('#date_end').val();

    if (oneData == '' || twoData == '') {

      $('#stats_info').text('Выберите период');
      setTimeout(function() {        
        $('#stats_info').text('');
      }, 3000);

    } else {
   
      $.ajax({
        method: "POST",
        url: "Board/statsSys",
        data: { 
          date_start: oneData,
          date_end: twoData
        }
      })

      .done(function(msg) {   
        let data = JSON.parse(msg);
        $('#count_link').text(data.countLink);
        $('#count_transitions').text(data.countDone);
        $('#count_fails').text(data.countFauled);
        $('#income_time').text(data.income);
        $('#total_income').text(data.totalIncome);
        $('#date_start').val('');
        $('#date_end').val('');
      });
    }
  });
});

//копирование ссылки по клику
$(document).ready(function() {
  $("#link_tag").click(function() {
      let text = $(this).text();
      let tempTextarea = $("<textarea>");

      tempTextarea.val(text);
      $("body").append(tempTextarea);
      tempTextarea.select();
      document.execCommand("copy");
      tempTextarea.remove();

      $('#offer_info').text('Ссылка скопирована в буфер!');
      setTimeout(function() {        
        $('#offer_info').text('');
      }, 3000);
  });
});

// кнопка подписки на заказ
$(document).ready(function(){
  $('#subs_offer').click(function(){

    let selectedOffer = $('#active_subs').find('option:selected').data('offer');  

    if (typeof selectedOffer !== 'undefined' && selectedOffer !== null) {

      $.ajax({
        method: "POST",
        url: "Board/subscribeOffer",
        data: { 
          user_id: user_id,
          offer: selectedOffer
        }
      })

      .done(function(msg) {   
        let data = JSON.parse(msg);
        let message = data.message;
        let offersFree = data.offersFree;
        let offersInwork = data.offersInwork;
        let link = data.link;

        $('#offer_info').text(message);
        setTimeout(function() {          
          $('#offer_info').text('');
        }, 3000);

        $('#link_tag').text(link);

        $('#active_subs').empty();
        $('#my_subs').empty();

        offersFree.forEach(function (offer) {
          let option = document.createElement('option');
          option.setAttribute('data-offer', offer.id);          
          option.innerText = '"' + offer.name + '" | Тема: "' + offer.topic + '" | Цена: ' + offer.cost_per_click;
          $('#active_subs').append(option);
        });      

        offersInwork.forEach(function (offer) {
          let option = document.createElement('option');
          option.setAttribute('data-offer', offer.id);
          option.innerText = '"' + offer.name + '" | Тема: "' + offer.topic + '" | Цена: ' + offer.cost_per_click;
          $('#my_subs').append(option);
        });
      });

    } else {
   
      $('#offer_info').text('Выбирете Заказ');
      setTimeout(function() {          
        $('#offer_info').text('');
      }, 3000);

    }
  });
});

// кнопка отписки от заказа
$(document).ready(function(){
  $('#unsubs_offer').click(function(){

    let selectedOffer = $('#my_subs').find('option:selected').data('offer');  

    if (typeof selectedOffer !== 'undefined' && selectedOffer !== null) {

      $.ajax({
        method: "POST",
        url: "Board/unsubscribeOffer",
        data: { 
          user_id: user_id,
          offer: selectedOffer
        }
      })

      .done(function(msg) {   
        let data = JSON.parse(msg);
        let message = data.message;
        let offersFree = data.offersFree;
        let offersInwork = data.offersInwork;        

        $('#offer_info').text(message);
        setTimeout(function() {          
          $('#offer_info').text('');
        }, 3000);
        
        $('#active_subs').empty();
        $('#my_subs').empty();

        offersFree.forEach(function (offer) {
          let option = document.createElement('option');
          option.setAttribute('data-offer', offer.id);          
          option.innerText = '"' + offer.name + '" | Тема: "' + offer.topic + '" | Цена: ' + offer.cost_per_click;
          $('#active_subs').append(option);
        });      

        offersInwork.forEach(function (offer) {
          let option = document.createElement('option');
          option.setAttribute('data-offer', offer.id);
          option.innerText = '"' + offer.name + '" | Тема: "' + offer.topic + '" | Цена: ' + offer.cost_per_click;
          $('#my_subs').append(option);
        });
      });

    } else {
   
      $('#offer_info').text('Выбирете Заказ');
      setTimeout(function() {          
        $('#offer_info').text('');
      }, 3000);

    }
  });
});

// кнопка получения ссылки
$(document).ready(function(){
  $('#link_offer').click(function(){

    let selectedOffer = $('#my_subs').find('option:selected').data('offer');  

    if (typeof selectedOffer !== 'undefined' && selectedOffer !== null) {

      $.ajax({
        method: "POST",
        url: "Board/getLink",
        data: { 
          user_id: user_id,
          offer: selectedOffer
        }
      })

      .done(function(msg) {   
        let data = JSON.parse(msg);        
        let link = data.link;
        $('#link_tag').text(link);
      });

    } else {
   
      $('#offer_info').text('Выбирете Заказ');
      setTimeout(function() {          
        $('#offer_info').text('');
      }, 3000);

    }
  });
});

//получение статистики Вебмастера
$(document).ready(function(){
  $('#statsDev').click(function(){
    let oneData = $('#date_start').val();
    let twoData = $('#date_end').val();

    if (oneData == '' || twoData == '') {

      $('#stats_dev_info').text('Выберите период');
      setTimeout(function() {        
        $('#stats_dev_info').text('');
      }, 3000);

    } else {
   
      $.ajax({
        method: "POST",
        url: "Board/statsDev",
        data: { 
          user_id: user_id,
          date_start: oneData,
          date_end: twoData
        }
      })

      .done(function(msg) {   
        let data = JSON.parse(msg);
        $('#transitions_dev').text(data.countRedirect);
        $('#income_dev').text(data.income);
        $('#date_start').val('');
        $('#date_end').val('');
      });
    }
  });
});

//Создание оффера или заказа
$(document).ready(function(){
  $('#createOffer').click(function(){    
    let name_offer = $('#name_offer').val();
    let prise_offer = $('#prise_offer').val();
    let target_link = $('#target_link').val();
    let topic_offer = $('#topic_offer').val();

    if (name_offer == '' || prise_offer == '' || target_link == '') {

      $('#offers_add_info').text('Пустые поля недопустимы');
      setTimeout(function() {        
        $('#offers_add_info').text('');
      }, 3000);

    } else {

      $.ajax({
        method: "POST",
        url: "Board/createOffer",
        data: { name: name_offer,
                topic: topic_offer,
                cost: prise_offer,
                url: target_link,
                id_user: user_id }
      })

      .done(function(msg) {      
        let data = JSON.parse(msg);
        let activeOffers = data.activeOffers;
        let allOffers = data.allOffers;
        let message = data.message;        
        
        $('#offers_add_info').text(message);
        setTimeout(function() {          
          $('#offers_add_info').text('');
        }, 3000);

        $('#current-active').empty();
        $('#all_subs').empty();

        activeOffers.forEach(function (offer) {
          let li = $('<li>').addClass('js-offers row ui-state-default').attr('data-offer', offer.id);
          let nameSpan = $('<span>').addClass('offer-name text-center').text(offer.name + ' | Цена="' + offer.cost_per_click + '"');
          let countSpan = $('<span>').addClass('offer-count-dev text-center').text('Подписано ' + offer.subscribers_count);
          li.append(nameSpan, countSpan);
          $('#current-active').append(li);
        });
        
        allOffers.forEach(function (offer) {
          let option = document.createElement('option');
          option.setAttribute('data-offer', offer.id);
          option.innerText = '"' + offer.name + '" | Цена ' + offer.cost_per_click + ' | Подписок ' + offer.subscribers_count;
          $('#all_subs').append(option);         
        });

        $('#name_offer').val('');
        $('#prise_offer').val('');
        $('#target_link').val('');
        $('#topic_offer').val('');
      });
    }
  });
});

// меню с не активными заказами
$( function() {
  $( "#current-inactive" ).sortable({
    connectWith: ".connectedSortable",
    receive: function(event, ui) {      
      let droppedItem = ui.item;      
      let offerId = droppedItem.data('offer');      
    
      $.ajax({
        method: "POST",
        url: "Board/deactivationOffer",
        data: { id_offer: offerId }
      })

      .done(function() {
        $('#offers_swap_info').text('Заказ успешно деактивирован!');
        setTimeout(function() {        
          $('#offers_swap_info').text('');
        }, 3000);
      });
    }
  }).disableSelection();
});

// меню с активными заказами
$( function() {
  $( "#current-active" ).sortable({
    connectWith: ".connectedSortable",
    receive: function(event, ui) {
      
      let droppedItem = ui.item;     
      let offerId = droppedItem.data('offer');      
     
      $.ajax({
        method: "POST",
        url: "Board/activationOffer",
        data: { id_offer: offerId }
      })

      .done(function() {
        $('#offers_swap_info').text('Заказ успешно активирован!');
        setTimeout(function() {        
          $('#offers_swap_info').text('');
        }, 3000);
      });
    }
  }).disableSelection();
});

//получение статистики Рекламодателя
$(document).ready(function(){
  $('#statsOffer').click(function(){
    let oneData = $('#date_start').val();
    let twoData = $('#date_end').val();
    let selectedOffer = $('#all_subs').find('option:selected').data('offer'); 

    if (selectedOffer === undefined || selectedOffer === null || oneData === '' || twoData === '') {

      console.log(oneData, twoData, selectedOffer);
      $('#offers_adv_info').text('Выберите период и заказ');
      setTimeout(function() {        
        $('#offers_adv_info').text('');
      }, 3000);

    } else {
   
      $.ajax({
        method: "POST",
        url: "Board/statsAdv",
        data: { 
          id_offer: selectedOffer,
          date_start: oneData,
          date_end: twoData
        }
      })

      .done(function(msg) {   
        let data = JSON.parse(msg);
        $('#transitions_adv').text(data.countRedirect);
        $('#expenses_adv').text(data.expenses);
        $('#date_start').val('');
        $('#date_end').val('');
      });
    }
  });
});