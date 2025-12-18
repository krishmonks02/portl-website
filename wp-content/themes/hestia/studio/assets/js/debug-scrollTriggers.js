
// Run this in browser console AFTER page loads
function checkAllTriggers() {
  const triggers = ScrollTrigger.getAll();
  console.group('ScrollTrigger Diagnostics');

  triggers.forEach((st, i) => {
    console.group(`ST #${i}`);
    console.log('Trigger element:', st.trigger.className || st.trigger.id || st.trigger.tagName);
    console.log('Start:', st.start);
    console.log('End:', st.end);

    // Try to get numeric values
    try {
      const startNum = typeof st.start === 'function' ? st.start() : st.start;
      const endNum = typeof st.end === 'function' ? st.end() : st.end;
      console.log('Start (num):', startNum);
      console.log('End (num):', endNum);
      console.log('Duration:', endNum - startNum);
    } catch (e) {
      console.log('Error calculating:', e.message);
    }

    console.groupEnd();
  });

  console.groupEnd();
}

// Debugging: uncomment to debug ScrollTriggers
// setTimeout(checkAllTriggers, 1000);
