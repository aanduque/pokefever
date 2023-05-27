import domReady from '@wordpress/dom-ready'

interface PokefeverSettings {
  ajax_url: string,
  nonce: string,
  current_post_id: number,
  messages: {
    403: string,
  }
}

const initializeAjaxFilter = () => {

  const filterElement = document.getElementById('filter') as HTMLFormElement | null

  filterElement?.addEventListener('submit', (event: Event) => {

    event.preventDefault()

    const focusedElement = document.activeElement?.getAttribute('name')

    const parentElement = filterElement.parentElement

    if (parentElement) {
      parentElement.style.transition = 'opacity 0.5s ease-in-out'
      parentElement.classList.add('opacity-50')
    }

    const formData = new FormData(filterElement as HTMLFormElement)

    const formAction = new URL(filterElement?.action ?? '')

    // Remove all search params
    formAction.search = ''

    fetch(formAction, {
      method: 'POST',
      body: formData,
    }).then((response) => response.text())
      .then((html) => {

        console.log(html)

        const parser = new DOMParser()

        const responseDOM = parser.parseFromString(html, "text/html")

        const mainListElement = responseDOM.getElementById('list') ?? null

        if (mainListElement) {

          const mainList = document.getElementById('list') as HTMLUListElement

          mainList.innerHTML = mainListElement.innerHTML

          if (parentElement) {
            parentElement.style.transition = 'opacity 0.5s ease-in-out'
            parentElement.classList.remove('opacity-50')
          }

          /**
           * Focus on the element that was focused before the form was submitted.
           */
          focusedElement && document.getElementsByName(focusedElement)[0]?.focus()

          initializeAjaxFilter()

        }

      })
      .catch((error) => {
        console.log(error)
      })

  })

}
// declare const pokefever: PokefeverSettings

domReady(initializeAjaxFilter)

jQuery(($: JQueryStatic) => {
  const loadOldPokedexNumber = (event: JQuery.ClickEvent<HTMLElement, undefined, HTMLElement, HTMLElement>): void => {

    event.preventDefault()

    $('#load-oldest-pokedex-number').hide(0)

    $('#oldest-pokedex-number').show(0)

    const data = {
      action: 'load_oldest_pokedex_number',
      post_id: pokefever.current_post_id,
      _ajax_nonce: pokefever.nonce,
    }

    $.post(pokefever.ajax_url, data)
      .done(function (response: string) {
        $('#oldest-pokedex-number').html(response)
      })
      .fail((jqxhr: JQueryXHR) => {
        $('#oldest-pokedex-number').html(pokefever.messages[jqxhr.status])
        setTimeout(() => {
          $('#oldest-pokedex-number').hide(0)
          $('#load-oldest-pokedex-number').show(0)
        }, 3000);
      })

  }

  $('#load-oldest-pokedex-number').on('click', loadOldPokedexNumber)
})