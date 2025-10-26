<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 to-purple-100 text-gray-800 p-4">
    <!-- Header -->
    <div class="text-center mb-6">
      <h1 class="text-3xl font-bold text-purple-700">🌟 Gangetabel 🌟</h1>
    </div>

    <!-- Game State Display -->
    <div v-if="!gameStarted" class="text-center mb-8">
      <p class="text-xl mb-4 text-purple-600">🎯 Klar til at øve gangetabellen? 🎯</p>
      <button
        @click="startGame"
        class="bg-gradient-to-r from-green-400 to-green-500 hover:from-green-500 hover:to-green-600 text-white px-8 py-3 rounded-lg text-xl font-bold transition-all transform hover:scale-105 shadow-lg mb-4"
      >
        🚀 Start Spil
      </button>
      <div>
        <button
          @click="showStats = true"
          class="bg-gradient-to-r from-blue-400 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white px-4 py-2 rounded-lg transition-all transform hover:scale-105 font-semibold shadow-lg"
        >
          📊 Resultat
        </button>
      </div>
    </div>

    <div v-else-if="gameCompleted" class="text-center mb-8">
      <h2 class="text-2xl font-bold mb-4 text-green-600">🎉 Tillykke! Du er færdig! 🎉</h2>
      <div class="bg-white p-6 rounded-lg inline-block shadow-xl border-4 border-yellow-300">
        <p class="text-lg mb-2 text-blue-600 font-semibold">⏰ Tid: {{ formatTime(finalTime) }}</p>
        <p class="text-lg mb-4 text-pink-600 font-semibold">❌ Fejl: {{ finalMistakes }}</p>
        <div v-if="newRecord" class="text-orange-600 font-bold mb-4 celebrate text-xl">
          🏆 {{ newRecord }} 🏆
        </div>
        <button
          @click="resetGame"
          class="bg-gradient-to-r from-green-400 to-green-500 hover:from-green-500 hover:to-green-600 text-white px-6 py-2 rounded-lg transition-all transform hover:scale-105 font-semibold shadow-lg"
        >
          🔄 Spil Igen
        </button>
      </div>
    </div>

    <div v-else class="text-center mb-6">
      <p class="text-2xl font-bold pulse-question text-purple-700 bg-yellow-100 px-6 py-3 rounded-lg inline-block border-3 border-yellow-300 shadow-lg"
         :class="{ 'flash-correct-question': questionFlashCorrect, 'flash-wrong-question': questionFlashWrong }">
        🤔 {{ currentQuestion.a }} × {{ currentQuestion.b }} =
        <span v-if="currentInput" class="text-green-600 bg-green-200 px-2 py-1 rounded">{{ currentInput }}</span>
        <span v-else class="text-gray-400">?</span>
      </p>
      <div class="mt-4 text-sm text-gray-600">
        💡 Skriv dit svar og tryk Enter
      </div>
    </div>

    <!-- Multiplication Table Grid -->
    <div class="flex justify-center">
      <div class="relative">
        <!-- Stats bar positioned above the table -->
        <div v-if="gameStarted && !gameCompleted" class="flex justify-between items-center mb-4 px-2" style="width: 100%;">
          <div class="bg-green-200 text-green-700 px-3 py-1 rounded-lg border-2 border-green-300 font-semibold text-sm">
            ✅ {{ answeredCount }}/81
          </div>
          <div class="bg-yellow-200 text-orange-700 px-3 py-1 rounded-lg border-2 border-yellow-300 font-semibold text-sm">
            ⏰ Tid: {{ formatTime(gameTime) }}
          </div>
          <div class="bg-pink-200 text-pink-700 px-3 py-1 rounded-lg border-2 border-pink-300 font-semibold text-sm">
            ❌ Fejl: {{ mistakes }}
          </div>
          <button
            @click="showStats = true"
            class="bg-gradient-to-r from-blue-400 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white px-3 py-1 rounded-lg transition-all transform hover:scale-105 font-semibold shadow-lg text-sm"
          >
            📊 Resultat
          </button>
        </div>

        <div class="grid grid-cols-10 gap-2 bg-white p-6 rounded-xl shadow-2xl border-4 border-purple-200">
        <!-- Header row -->
        <div class="w-12 h-12"></div>
        <div
          v-for="col in 9"
          :key="`header-${col}`"
          class="w-12 h-12 flex items-center justify-center font-bold text-purple-600 bg-purple-100 rounded-lg border-2 border-purple-200"
          :class="{ 'bg-yellow-300 border-yellow-400 text-orange-700': isColumnHighlighted(col) }"
        >
          {{ col }}
        </div>

        <!-- Data rows -->
        <template v-for="row in 9" :key="`row-${row}`">
          <!-- Row header -->
          <div
            class="w-12 h-12 flex items-center justify-center font-bold text-purple-600 bg-purple-100 rounded-lg border-2 border-purple-200"
            :class="{ 'bg-yellow-300 border-yellow-400 text-orange-700': isRowHighlighted(row) }"
          >
            {{ row }}
          </div>

          <!-- Data cells -->
          <div
            v-for="col in 9"
            :key="`cell-${row}-${col}`"
            class="w-12 h-12 flex items-center justify-center rounded-lg border-2 transition-all duration-200 font-semibold"
            :class="getCellClass(row, col)"
          >
            {{ getCellValue(row, col) }}
          </div>
        </template>
        </div>
      </div>
    </div>

    <!-- Stats Modal -->
    <div v-if="showStats" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-xl max-w-md w-full mx-4 shadow-2xl border-4 border-blue-200">
        <h3 class="text-xl font-bold mb-4 text-blue-700">🏆 Top 10 Resultater 🏆</h3>
        <div v-if="stats.length === 0" class="text-gray-500 text-center py-4">
          🎯 Ingen resultater endnu
        </div>
        <div v-else class="space-y-2">
          <div
            v-for="(stat, index) in stats.slice(0, 10)"
            :key="index"
            class="flex justify-between bg-gradient-to-r from-blue-50 to-purple-50 p-3 rounded-lg border-2 border-blue-100"
          >
            <span class="font-bold text-purple-600">🥇 #{{ index + 1 }}</span>
            <span class="text-blue-600">⏰ {{ formatTime(stat.time) }}</span>
            <span class="text-pink-600">❌ {{ stat.mistakes }} fejl</span>
          </div>
        </div>
        <button
          @click="showStats = false"
          class="mt-4 bg-gradient-to-r from-blue-400 to-blue-500 hover:from-blue-500 hover:to-blue-600 text-white px-4 py-2 rounded-lg transition-all transform hover:scale-105 w-full font-semibold shadow-lg"
        >
          ✖️ Luk
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

// Game state
const gameStarted = ref(false)
const gameCompleted = ref(false)
const gameTime = ref(0)
const mistakes = ref(0)
const finalTime = ref(0)
const finalMistakes = ref(0)
const newRecord = ref('')
const showStats = ref(false)

// Game logic
const questions = ref<Array<{a: number, b: number, answered: boolean}>>([])
const currentQuestionIndex = ref(0)
const answeredCells = ref<{[key: string]: number}>({})
const gameTimer = ref<number | null>(null)

// Current question
const currentQuestion = computed(() => {
  if (questions.value.length === 0 || currentQuestionIndex.value >= questions.value.length) {
    return { a: 1, b: 1 }
  }
  return questions.value[currentQuestionIndex.value]
})

// Count of answered questions
const answeredCount = computed(() => {
  return questions.value.filter(q => q.answered).length
})

// Statistics
const stats = ref<Array<{time: number, mistakes: number, date: string}>>([])

// Initialize questions (all 81 combinations)
const initializeQuestions = () => {
  const allQuestions = []
  for (let a = 1; a <= 9; a++) {
    for (let b = 1; b <= 9; b++) {
      allQuestions.push({ a, b, answered: false })
    }
  }

  // Shuffle the questions
  for (let i = allQuestions.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[allQuestions[i], allQuestions[j]] = [allQuestions[j], allQuestions[i]]
  }

  questions.value = allQuestions
}

// Game controls
const startGame = () => {
  gameStarted.value = true
  gameCompleted.value = false
  gameTime.value = 0
  mistakes.value = 0
  currentQuestionIndex.value = 0
  answeredCells.value = {}
  newRecord.value = ''
  currentInput.value = ''

  initializeQuestions()

  // Start timer
  gameTimer.value = setInterval(() => {
    gameTime.value++
  }, 1000)
}

const resetGame = () => {
  gameStarted.value = false
  gameCompleted.value = false
  gameTime.value = 0
  mistakes.value = 0
  currentQuestionIndex.value = 0
  answeredCells.value = {}
  newRecord.value = ''
  currentInput.value = ''

  if (gameTimer.value) {
    clearInterval(gameTimer.value)
    gameTimer.value = null
  }
}

// Cell styling and highlighting
const getCellClass = (row: number, col: number) => {
  const isCurrentCell = currentQuestion.value.a === row && currentQuestion.value.b === col
  const isHighlighted = isRowHighlighted(row) || isColumnHighlighted(col)
  const cellKey = `${row}-${col}`
  const isAnswered = cellKey in answeredCells.value

  return {
    'bg-green-500 text-white font-bold border-green-400 shadow-lg transform scale-105': isCurrentCell,
    'bg-yellow-200 border-yellow-300 text-orange-700': isHighlighted && !isCurrentCell,
    'bg-blue-100 border-blue-200 text-blue-700': isAnswered && !isCurrentCell && !isHighlighted,
    'bg-gray-50 border-gray-200 text-gray-600': !isCurrentCell && !isHighlighted && !isAnswered,
    'flash-correct': isCurrentCell && isFlashing.value,
    'shake-wrong': isCurrentCell && isShaking.value
  }
}

const getCellValue = (row: number, col: number) => {
  const cellKey = `${row}-${col}`
  if (cellKey in answeredCells.value) {
    return answeredCells.value[cellKey]
  }
  return ''
}

const isRowHighlighted = (row: number) => {
  return gameStarted.value && !gameCompleted.value && currentQuestion.value.a === row
}

const isColumnHighlighted = (col: number) => {
  return gameStarted.value && !gameCompleted.value && currentQuestion.value.b === col
}

// Animation state
const isFlashing = ref(false)
const isShaking = ref(false)
const questionFlashCorrect = ref(false)
const questionFlashWrong = ref(false)

// Answer handling
const handleAnswer = (answer: number) => {
  const correct = currentQuestion.value.a * currentQuestion.value.b
  const cellKey = `${currentQuestion.value.a}-${currentQuestion.value.b}`

  if (answer === correct) {
    // Correct answer - flash question green
    questionFlashCorrect.value = true
    answeredCells.value[cellKey] = answer
    questions.value[currentQuestionIndex.value].answered = true

    // Flash animation on grid cell
    isFlashing.value = true

    setTimeout(() => {
      questionFlashCorrect.value = false
      isFlashing.value = false
      currentInput.value = ''

      // Check if game is completed - all questions must be answered
      const allAnswered = questions.value.every(q => q.answered)
      if (allAnswered) {
        completeGame()
      } else {
        // Find next unanswered question
        const nextIndex = questions.value.findIndex((q, index) =>
          index > currentQuestionIndex.value && !q.answered
        )
        if (nextIndex !== -1) {
          currentQuestionIndex.value = nextIndex
        } else {
          // If no questions after current index, find first unanswered from beginning
          const firstUnanswered = questions.value.findIndex(q => !q.answered)
          if (firstUnanswered !== -1) {
            currentQuestionIndex.value = firstUnanswered
          }
        }
      }
    }, 400)
  } else {
    // Wrong answer - flash question red
    mistakes.value++
    questionFlashWrong.value = true
    isShaking.value = true

    setTimeout(() => {
      questionFlashWrong.value = false
      isShaking.value = false
      currentInput.value = ''
    }, 800)
  }
}

const completeGame = () => {
  gameCompleted.value = true
  finalTime.value = gameTime.value
  finalMistakes.value = mistakes.value

  if (gameTimer.value) {
    clearInterval(gameTimer.value)
    gameTimer.value = null
  }

  // Save statistics and check for records
  saveGameStats()
}

// Statistics management
const saveGameStats = () => {
  const newStat = {
    time: finalTime.value,
    mistakes: finalMistakes.value,
    date: new Date().toISOString()
  }

  stats.value.push(newStat)
  stats.value.sort((a, b) => {
    // Sort by mistakes first, then by time
    if (a.mistakes !== b.mistakes) {
      return a.mistakes - b.mistakes
    }
    return a.time - b.time
  })

  // Check for records
  if (stats.value.length === 1) {
    newRecord.value = 'Første resultat!'
  } else {
    const bestTime = Math.min(...stats.value.slice(0, -1).map(s => s.time))
    const bestMistakes = Math.min(...stats.value.slice(0, -1).map(s => s.mistakes))

    if (finalMistakes.value < bestMistakes) {
      newRecord.value = 'Ny rekord i færrest fejl!'
    } else if (finalMistakes.value === bestMistakes && finalTime.value < bestTime) {
      newRecord.value = 'Ny rekord i tid!'
    }
  }

  // Save to cookie
  saveStatsToCookie()
}

const saveStatsToCookie = () => {
  const cookieValue = JSON.stringify(stats.value)
  document.cookie = `gangetabel_stats=${encodeURIComponent(cookieValue)}; path=/; max-age=${365 * 24 * 60 * 60}`
}

const loadStatsFromCookie = () => {
  const cookies = document.cookie.split(';')
  const statsCookie = cookies.find(cookie => cookie.trim().startsWith('gangetabel_stats='))

  if (statsCookie) {
    try {
      const cookieValue = decodeURIComponent(statsCookie.split('=')[1])
      stats.value = JSON.parse(cookieValue)
    } catch (error) {
      console.error('Error loading stats from cookie:', error)
      stats.value = []
    }
  }
}

// Multi-digit input handling
const currentInput = ref('')

// Keyboard input handling
const handleKeyDown = (event: KeyboardEvent) => {
  if (!gameStarted.value || gameCompleted.value) return

  const key = event.key

  if (key >= '0' && key <= '9') {
    // Prevent adding more than 2 digits (max answer is 81)
    if (currentInput.value.length < 2) {
      currentInput.value += key
    }
  } else if (key === 'Enter') {
    if (currentInput.value) {
      handleAnswer(parseInt(currentInput.value))
    }
  } else if (key === 'Backspace') {
    event.preventDefault() // Prevent browser back navigation
    currentInput.value = currentInput.value.slice(0, -1)
  }
}

// Utility functions
const formatTime = (seconds: number) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

// Lifecycle
onMounted(() => {
  loadStatsFromCookie()
  document.addEventListener('keydown', handleKeyDown)
})

onUnmounted(() => {
  if (gameTimer.value) {
    clearInterval(gameTimer.value)
  }
  document.removeEventListener('keydown', handleKeyDown)
})
</script>

<style scoped>
/* Flash animation for correct answers */
@keyframes flash-correct {
  0% { background-color: rgb(34 197 94); box-shadow: 0 0 20px rgb(74 222 128); }
  50% { background-color: rgb(74 222 128); box-shadow: 0 0 30px rgb(34 197 94); }
  100% { background-color: rgb(34 197 94); box-shadow: 0 0 20px rgb(74 222 128); }
}

/* Flash animation for correct question */
@keyframes flash-correct-question {
  0% { background-color: rgb(254 240 138); border-color: rgb(253 224 71); }
  50% { background-color: rgb(134 239 172); border-color: rgb(74 222 128); transform: scale(1.05); }
  100% { background-color: rgb(254 240 138); border-color: rgb(253 224 71); }
}

/* Flash animation for wrong question */
@keyframes flash-wrong-question {
  0% { background-color: rgb(254 240 138); border-color: rgb(253 224 71); }
  50% { background-color: rgb(252 165 165); border-color: rgb(248 113 113); transform: scale(1.05); }
  100% { background-color: rgb(254 240 138); border-color: rgb(253 224 71); }
}

/* Shake animation for wrong answers */
@keyframes shake {
  0%, 100% { transform: translateX(0); background-color: rgb(239 68 68); }
  25% { transform: translateX(-5px); background-color: rgb(248 113 113); }
  75% { transform: translateX(5px); background-color: rgb(239 68 68); }
}

/* Pulse animation for current question */
@keyframes pulse-question {
  0%, 100% { transform: scale(1); box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4); }
  50% { transform: scale(1.05); box-shadow: 0 8px 25px rgba(168, 85, 247, 0.6); }
}

.flash-correct {
  animation: flash-correct 0.5s ease-in-out;
}

.shake-wrong {
  animation: shake 0.5s ease-in-out;
}

.pulse-question {
  animation: pulse-question 2s ease-in-out infinite;
}

.flash-correct-question {
  animation: flash-correct-question 0.4s ease-in-out;
}

.flash-wrong-question {
  animation: flash-wrong-question 0.8s ease-in-out;
}

/* Smooth transitions */
.grid > div {
  transition: all 0.3s ease-in-out;
}

/* Hover effects for interactive elements */
button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Record celebration animation */
@keyframes celebrate {
  0%, 100% { transform: scale(1) rotate(0deg); }
  25% { transform: scale(1.1) rotate(-5deg); color: rgb(249 115 22); }
  75% { transform: scale(1.1) rotate(5deg); color: rgb(234 179 8); }
}

.celebrate {
  animation: celebrate 0.6s ease-in-out infinite;
}

/* Fun bounce effect for buttons */
@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

button:active {
  animation: bounce 0.3s ease-in-out;
}
</style>
