//
//  QuestionDetailView.swift
//  Voting
//
//  Created by Szabolcs Tóth on 15.10.2022.
//  Copyright © 2022 Szabolcs Tóth. All rights reserved.
//

import SwiftUI

struct QuestionDetailView: View {
    /// Properties
    let question: Question
    let vote: [Vote]
    
    var body: some View {
        content
    }
    
    @ViewBuilder var content: some View {
        VStack {
            Text(question.questionText)
        }
    }
}

struct QuestionDetailsView_Previews: PreviewProvider {
    static var previews: some View {
        QuestionDetailView(question: Question.dummyQuestion1, vote: [Vote(id: 1, voteText: "Test text", numberOfVotes: "2", questionID: 1)])
    }
}
